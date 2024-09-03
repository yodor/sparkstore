<?php
include_once("store/pages/ProductPageBase.php");

include_once("components/NestedSetTreeView.php");
include_once("components/renderers/items/TextTreeItem.php");
include_once("components/Action.php");
include_once("components/TableView.php");
include_once("components/ItemView.php");

include_once("components/ClosureComponent.php");

include_once("utils/GetProcessorCollection.php");

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
    protected ?ProductListFilter $filters;

    /**
     * Products list component
     * @var ItemView|null
     */
    protected $view = NULL;

    protected GetProcessorCollection $property_filter;

    protected GETProcessor $category_filter;

    public $treeViewUseAgregateSelect = true;


    public function __construct()
    {
        parent::__construct();

        $this->property_filter = new GetProcessorCollection();

        $section_filter = new GETProcessor("section", "section");
        $closure = function(GETProcessor $filter) {
            $clause = new SQLClause();
            $value = $filter->getValue();
            $clause->setExpression("product_sections LIKE '%$value%'", "", "");
            $filter->getClauseCollection()->append($clause);
        };
        $section_filter->setClosure($closure);
        $this->property_filter->append($section_filter);

//        $clause = new SQLClause();
//        $clause->setExpression("(discount_percent > 0 OR promo_price > 0)", "", "");
//
//        $filter = new GETProcessor("Промо", "promo");
//        $filter->setClosure(null);
//        $filter->getClauseCollection()->append($clause);
//
//        $this->property_filter->append($filter);

        $this->category_filter = new GETProcessor("catID", "catID");


        //Initialize product categories tree
        $treeView = new NestedSetTreeView();
        $treeView->setCacheable(true);

        $treeView->setName("products_tree");

        //item renderer for the tree view url will be set in cleanupCategoryURL
        $ir = new TextTreeItem();
        $ir->setLabelKey("category_name");
        $ir->getTextAction()->addDataAttribute("title", "category_name");

        $treeView->setItemRenderer($ir);

        $this->treeView = $treeView;


        //empty filters - renderer iterators are not set yet
        $this->filters = new ProductListFilter();


        $this->view = new ItemView();
        $this->view->setCacheable(true);
        
        $this->view->setItemRenderer(new ProductListItem());
        $this->view->setItemsPerPage(24);

        //disable list/grid
        $this->view->getTopPaginator()->view_modes_enabled = FALSE;

        $this->head()->addCSS(STORE_LOCAL . "/css/product_list.css");
        $this->head()->addJS(STORE_LOCAL . "/js/product_list.js");

        $this->canonical_enabled = true;

        $this->canonical_params = array($this->category_filter->getName(), Paginator::KEY_PAGE);

    }

    public function getItemView() : ItemView
    {
        return $this->view;
    }

    public function getTreeView() : NestedSetTreeView
    {
        return $this->treeView;
    }

    /**
     * Initialize the list view sorting fields
     * @return void
     */
    protected function initSortFields()
    {
        $sort_prod = new PaginatorSortField("prodID", "Най-нови", "", "DESC");
        $this->view->getPaginator()->addSortField($sort_prod);

        $sort_price = new PaginatorSortField("sell_price", "Цена", "", "ASC");
        $this->view->getPaginator()->addSortField($sort_price);
    }


    /**
     * Post-constructor initialization
     * @return void
     * @throws Exception
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
        //$products_list->group_by = SellableProducts::DefaultGrouping();

        //echo $products_list->getSQL();
        $this->view->setIterator(new SQLQuery($products_list, "prodID"));

        $this->initSortFields();

        $this->section = "";
    }

    public function GetProcessorCollection() : GetProcessorCollection
    {
        return $this->property_filter;
    }

    /**
     * Process the page input
     * Calls prepare Keywords and prepare Description to set the values of meta-keywords and meta-description
     * @return void
     * @throws Exception
     */
    public function processInput()
    {

        //filter precedence
        // 0 property filters
        // 3 keyword search
        // 5 attribute filters

        $iterator = $this->property_filter->iterator();
        while ($iterator->valid()) {
            $filter = $iterator->current();
            if ($filter instanceof GETProcessor) {
                $filter->setSQLSelect($this->select);
                $filter->processInput();
            }
            $iterator->next();
        }

        $section_filter = $this->property_filter->get("section");
        if ($section_filter->isProcessed()) {
            $this->section = $section_filter->getValue();
        }

        $this->category_filter->processInput();

        if ($this->category_filter->isProcessed()) {
            $this->treeView->setSelectedID($this->category_filter->getValue());
        }


        $this->keyword_search->processInput();

        if ($this->keyword_search->isProcessed()) {
            $cc = $this->keyword_search->getForm()->prepareClauseCollection("AND");
            $cc->copyTo($this->select->where());
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
//        $this->select->group_by = SellableProducts::DefaultGrouping();

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
        $this->prepareDescription();

        $this->processTreeViewURL();
    }

    /**
     * Set clean URL for tree view items
     * Only transfer parameters that affect the query of the treeview
     * @return void
     */
    protected function processTreeViewURL() : void
    {

        $supported_params = array();
        $supported_params[] = $this->category_filter->getName();

        //append property filter names
        $iterator = $this->property_filter->iterator();
        while ($iterator->valid()) {
            $filter = $iterator->current();
            if ($filter instanceof GETProcessor) {
                $supported_params[] = $filter->getName();
            }
            $iterator->next();
        }

        //append dynamic filter names
        if ($this->filters) {
            foreach ($this->filters->getForm()->getInputNames() as $idx => $name) {
                $supported_params[] = $name;
            }
        }

        //keyword search
        if ($this->keyword_search) {
            foreach ($this->keyword_search->getForm()->getInputNames() as $idx => $name) {
                $supported_params[] = $name;
            }
            $supported_params[] = KeywordSearch::SUBMIT_KEY;
        }

        //do not set paginator names
//        $view_params = $this->view->getPaginator()->getParameterNames();
//        foreach ($view_params as $idx=>$name) {
//            if (str_contains($name, Paginator::KEY_PAGE))continue;
//            $supported_params[] = $name;
//        }


        $item = $this->treeView->getItemRenderer();
        if ($item instanceof TextTreeItem) {

            $itemURL = $item->getTextAction()->getURLBuilder();

            $pageURL = SparkPage::Instance()->getURL();

            //static url parameter names from the current page
            $page_params = $pageURL->getParameterNames();
            //cleanup non supported names
            foreach ($page_params as $idx=>$name) {
                if (!in_array($name, $supported_params)) {
                    $pageURL->remove($name);
                }
            }
            //
            $itemURL->buildFrom($pageURL->url());
            $itemURL->add(new DataParameter("catID"));
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

    /**
     * Renders list of sub-categories with image or all categories if no category is selected
     * @return void
     * @throws Exception
     */
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



    protected function loadCategoryPath(int $nodeID) : void
    {
        parent::loadCategoryPath($nodeID);
        if (is_array($this->category_path) && count($this->category_path)>0) {
            $length = count($this->category_path);
            $this->view->setName($this->category_path[$length-1]["category_name"]);
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

                echo "<div class='Caption' >";

                    echo "<div class='toggle' onclick='togglePanel(this)'>";
                        echo "<label>";
                        echo tr("Категории");
                        echo "</label>";
                    echo "</div>";

                    echo "<span>";
                    echo tr("Категории");
                    echo "</span>";

                echo "</div>";


                echo "<div class='viewport'>";
                $this->renderCategoriesTree();
                echo "</div>";

            echo "</div>"; //categories panel


            if ($this->filters instanceof ProductListFilter) {
                echo "<div class='filters panel'>";

                echo "<div class='Caption' >";
                    echo "<div class='toggle' onclick='togglePanel(this)'>";
                        echo "<label>";
                        echo tr("Филтри");
                        echo "</label>";
                    echo "</div>";

                    echo "<span>";
                    echo tr("Филтри");
                    echo "</span>";
                echo "</div>";

                echo "<div class='viewport'>";
                $this->renderProductFilters();
                echo "</div>";

                echo "</div>";//filters

                $active_filters = $this->filters->getActiveFilters();
                if (count($active_filters)>0) {
                    echo "<div class='active_filters panel'>";

                    $viewCaption = "";
                    foreach ($active_filters as $flabel=>$fvalue) {
                        $viewCaption.= tr($flabel).": ".$fvalue."; ";
                    }
                    echo "<div class='Caption'>".$viewCaption."</div>";

                    echo "</div>";
                }
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
