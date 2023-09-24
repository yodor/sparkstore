<?php
include_once("store/pages/ProductPageBase.php");

include_once("components/NestedSetTreeView.php");
include_once("components/renderers/items/TextTreeItem.php");
include_once("components/Action.php");
include_once("components/TableView.php");
include_once("components/ItemView.php");

include_once("components/ClosureComponent.php");

include_once("utils/GETVariableFilter.php");

include_once("store/components/ProductListFilter.php");


class ProductListPageBase extends ProductPageBase
{


    /**
     * @var NestedSetTreeView|null
     */
    protected $treeView = NULL;

    /**
     * Used to render products/tree/filters
     * @var SQLSelect|null
     */
    protected $select = NULL;

    /**
     * Filters form component
     * @var ProductListFilter|null
     */
    protected $filters = NULL;

    /**
     * Products list component
     * @var ItemView|null
     */
    protected $view = NULL;


    /**
     * @var GETVariableFilter|null
     */
    protected $section_filter = NULL;

    /**
     * @var GETVariableFilter|null
     */
    protected $category_filter = NULL;


    public $treeViewUseAgregateSelect = true;


    public function __construct()
    {
        parent::__construct();


        $this->section_filter = new GETVariableFilter("section", "section");

        $this->category_filter = new GETVariableFilter("catID", "catID");

        //Initialize product categories tree
        $treeView = new NestedSetTreeView();
        $treeView->setName("products_tree");

        //item renderer for the tree view
        $ir = new TextTreeItem();
        $ir->setLabelKey("category_name");
        $ir->getTextAction()->addDataAttribute("title", "category_name");
        $ir->getTextAction()->getURLBuilder()->add(new DataParameter("catID"));
        $ir->getTextAction()->getURLBuilder()->setClearPageParams(false);
        $ir->getTextAction()->getURLBuilder()->setClearParams(...array("page"));
        $treeView->setItemRenderer($ir);

        $this->treeView = $treeView;


        //empty filters - renderer iterators are not set yet
        $this->filters = new ProductListFilter();


        $this->view = new ItemView();
        $this->view->setItemRenderer(new ProductListItem());
        $this->view->setItemsPerPage(24);

        $this->view->getTopPaginator()->view_modes_enabled = TRUE;

        $this->addCSS(STORE_LOCAL . "/css/product_list.css");
        $this->addJS(STORE_LOCAL . "/js/product_list.js");

    }

    public function getItemView() : ItemView
    {
        return $this->view;
    }

    public function getTreeView() : NestedSetTreeView
    {
        return $this->treeView;
    }

    protected function initSortFields()
    {
        $sort_prod = new PaginatorSortField("prodID", "Най-нови", "", "DESC");
        $this->view->getPaginator()->addSortField($sort_prod);

        $sort_price = new PaginatorSortField("sell_price", "Цена", "", "ASC");
        $this->view->getPaginator()->addSortField($sort_price);
    }

    /**
     * Post CTOR initialization
     */
    public function initialize()
    {
        //main products select - no grouping here as filters are not applied yet
        if (is_null($this->bean)) {
            throw new Exception("List bean is not set");
        }
        $this->select = clone $this->bean->select();

        $search_fields = array("product_name", "keywords");
        $this->keyword_search->getForm()->setFields($search_fields);
        //$this->keyword_search->getForm()->setCompareExpression("relation.inventory_attributes", array("%:{keyword}|%", "%:{keyword}"));

        //default - all categories not filtered or aggregated
        $treeSelect = $this->product_categories->selectTree(array("category_name"));
        $treeQry = new SQLQuery($treeSelect, $this->product_categories->key(), $this->product_categories->getTableName());
        $this->treeView->setIterator($treeQry);

        //default products select all products from all categories
        $products_list = clone $this->select;
        $products_list->group_by = SellableProducts::DefaultGrouping();
        //echo $products_list->getSQL();
        $this->view->setIterator(new SQLQuery($products_list, "prodID"));

        $this->initSortFields();
    }


    //process get vars
    public function processInput()
    {

        //filter precedence
        // 1 section
        // 2 brand
        // 3 keyword search
        // 4 category
        // 5 attribute filters

        $this->section = "";


        $this->section_filter->processInput();

        if ($this->section_filter->isProcessed()) {

            $value = $this->section_filter->getValue();

            $qry = $this->sections->queryField("section_title", $value, 1);
            //section exists
            $num = $qry->exec();
            if ($num > 0) {
                $this->section = $value;
            }

            $this->select->where()->append("product_sections LIKE '%$value%'");
        }

        $this->keyword_search->processInput();

        if ($this->keyword_search->isProcessed()) {

            $cc = $this->keyword_search->getForm()->prepareClauseCollection("AND");
//            print_r($cc->getSQL());
            $cc->copyTo($this->select->where());
        }

        $this->category_filter->processInput();
        if ($this->category_filter->isProcessed()) {
            $this->treeView->setSelectedID((int)$this->category_filter->getValue());
        }


        $nodeID = $this->treeView->getSelectedID();
        if ($nodeID > 0) {
            $this->loadCategoryPath($nodeID);
        }

        //clone the main products select here to keep the tree siblings visible
        $products_tree = clone $this->select;

        //unset - will use catID and category name from selectChildNodesWith
        $this->select->fields()->unset("catID");
        $this->select->fields()->unset("category_name");

        $this->select = $this->product_categories->selectChildNodesWith($this->select, $this->bean->getTableName(), $nodeID, array("catID", "category_name"));

        if ($this->filters instanceof ProductListFilter) {

            //set initial products select. create attribute filters need to append the data inputs only.
            $this->filters->getForm()->setSQLSelect($this->select);
            $this->filters->getForm()->createAttributeFilters();
            $this->filters->getForm()->createVariantFilters();
            //update here if all filter values needs to be visible
            $this->filters->getForm()->updateIterators(true);

            //assign values from the query string to the data inputs
            $this->filters->processInput();

            $filters_where = $this->filters->getForm()->prepareClauseCollection(" AND ");
            //echo $filters_where->getSQL();
            //products list filtered
            $filters_where->copyTo($this->select->where());

            //tree view filtered
            $filters_where->copyTo($products_tree->where());


            //filter values will be limited to the selection only
            //set again - rendering will use this final select
            $this->filters->getForm()->setSQLSelect($this->select);
            $this->filters->getForm()->getGroup(ProductListFilterInputForm::GROUP_VARIANTS)->removeAll();
            $this->filters->getForm()->getGroup(ProductListFilterInputForm::GROUP_ATTRIBUTES)->removeAll();
            //create again to hide empty filters
            $this->filters->getForm()->createAttributeFilters();
            $this->filters->getForm()->createVariantFilters();
            $this->filters->getForm()->updateIterators(false);

            //assign values from the query string to the data inputs
            $this->filters->processInput();


        }

        //setup grouping for the list item view
        $this->select->group_by = SellableProducts::DefaultGrouping();

        //primary key is prodID as we group by prodID(Products) not piID(ProductInventory)
        $this->view->setIterator(new SQLQuery($this->select, "prodID"));

        //construct category tree for the products that will be listed
        //keep same grouping as the products list
        $products_tree->group_by = $this->select->group_by;
        //select only fields needed in the treeView iterator
        $products_tree->fields()->reset();
        $products_tree->fields()->set("prodID", "catID");
        //echo $products_tree->getSQL();

        $products_tree = $products_tree->getAsDerived();
        $products_tree->fields()->set("relation.prodID", "relation.catID");

        //needs getAsDerived - sets grouping and ordering on the returned select, suitable as treeView iterator
        $aggregateSelect = $this->product_categories->selectTreeRelation($products_tree, "relation", "prodID", array("category_name"));
        //echo $aggregateSelect->getSQL();

        //$aggregateSelect->fields()->removeValue("related_count");
        if ($this->treeViewUseAgregateSelect) {
            $this->treeView->setIterator(new SQLQuery($aggregateSelect, $this->product_categories->key()));
        }

        $this->prepareKeywords();
    }

    protected function prepareKeywords()
    {

        if (!$this->category_filter->getValue()) return "";

        $catID = $this->category_filter->getValue();

        $keywords = $this->getCategoryKeywords($catID);

        if($keywords) {
            $this->addMeta("keywords", prepareMeta($keywords));
        }
    }

    public function isProcessed(): bool
    {
        return $this->keyword_search->isProcessed();

    }

    public function renderCategoriesTree()
    {
        $this->treeView->render();
    }

    public function renderProductFilters()
    {
        if ($this->filters instanceof ProductListFilter) {
            $this->filters->render();
            echo "<button class='ColorButton' onClick='clearFilters()'>" . tr("Изчисти филтрите") . "</button>";
        }
    }

    public function renderChildCategories()
    {

        $sel_catID = $this->treeView->getSelectedID();
        $catsel = clone $this->treeView->getIterator()->select;

        $catsel->clearMode();
        $catsel->fields()->reset();
        $catsel->fields()->set("parent.catID", "parent.category_name", "parent.lft");
        $catsel->group_by = "node.catID";

        if ($sel_catID>0) {
            $catsel->fields()->reset();
            $catsel->fields()->set("node.catID", "node.category_name", "node.lft");
            $catsel->where()->add("parent.catID", $sel_catID);
            $catsel->where()->add("node.catID", $sel_catID, "<>");
            $catsel->group_by = "node.catID";
        }
        $catsel->order_by = "node.lft ASC";

        $catsel = $catsel->getAsDerived("topcats");
        $catsel->fields()->set("topcats.catID", "topcats.category_name", "topcats.lft");
        $catsel->fields()->setExpression("(SELECT pcp.pcpID FROM product_category_photos pcp WHERE pcp.catID = topcats.catID)", "pcpID");
        $catsel->group_by = "catID";
        $catsel->order_by = "lft ASC";

        //echo $catsel->getSQL();

        $query = new SQLQuery($catsel, "catID");
        $num = $query->exec();

        echo "<div class='category_list'>";
        $builder = new URLBuilder();
        $builder->buildFrom($this->getPageURL());
        $builder->add(new URLParameter("catID"));
        $si = new StorageItem();
        $si->className = "ProductCategoryPhotosBean";
        while ($result = $query->nextResult()) {
            $builder->get("catID")->setValue($result->get("catID"));
            $si->id = $result->get("pcpID");
            echo "<div class='item'>";
            echo "<a href='{$builder->url()}' title='{$result->get("category_name")}'><img src='{$si->hrefCrop(128,-1)}' alt='{$result->get("category_name")}'>{$result->get("category_name")}</a>";
            echo "</div>";
        }
        echo "</div>";

        
    }

    public function renderProductsView()
    {
        $this->view->render();
    }



    protected function loadCategoryPath(int $nodeID)
    {
        parent::loadCategoryPath($nodeID); // TODO: Change the autogenerated stub
        if ($this->category_path) {

            $length = count($this->category_path);
            if ($length>0) {
                $this->view->setName($this->category_path[$length-1]["category_name"]);
            }
        }

    }



    /**
     * Return the active selected section title
     * @return string
     */
    public function getSection() : string
    {
        return $this->section;
    }

    public function renderContents()
    {
        $this->renderCategoryPath();

        echo "<div class='column left' section='{$this->section}'>";

            echo "<div class='categories panel'>";

                echo "<div class='Caption' ><div class='toggle' onclick='togglePanel(this)'><div></div></div>" . tr("Категории") . "</div>";

                echo "<div class='viewport'>";
                $this->renderCategoriesTree();
                echo "</div>";

            echo "</div>"; //tree


            if ($this->filters instanceof ProductListFilter) {
                echo "<div class='filters panel'>";

                echo "<div class='Caption' ><div class='toggle' onclick='togglePanel(this)'><div></div></div>" . tr("Филтри") . "</div>";

                echo "<div class='viewport'>";
                $this->renderProductFilters();
                echo "</div>";

                echo "</div>";//filters
            }

        echo "</div>"; //column left

        echo "<div class='column product_list'>";

        $this->renderChildCategories();

        $this->renderProductsView();

        echo "</div>";

        Session::set("shopping.list", $this->getPageURL());

    }
}

?>
