<?php
include_once("class/pages/StorePage.php");

include_once("components/NestedSetTreeView.php");
include_once("components/renderers/items/TextTreeItem.php");
include_once("components/Action.php");
include_once("components/TableView.php");
include_once("components/ItemView.php");
include_once("components/BreadcrumbList.php");
include_once("components/ClosureComponent.php");

include_once("utils/GETVariableFilter.php");

include_once("store/components/ProductListFilter.php");
include_once("store/components/renderers/items/ProductListItem.php");
include_once("store/beans/SellableProducts.php");

class ProductListPageBase extends StorePage
{
    protected $products_title = "Продукти";

    /**
     * @var SellableProducts|null
     */
    protected $bean = null;

    /**
     * @var SectionsBean|null
     */
    public $sections = NULL;

    /**
     * @var ProductCategoriesBean
     */
    public $product_categories = NULL;


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
     * Currently selected section (processed as get variable)
     * @var string
     */
    protected $section = "";

    /**
     * @var GETVariableFilter|null
     */
    protected $section_filter = NULL;

    /**
     * @var GETVariableFilter|null
     */
    protected $category_filter = NULL;

    /**
     * Array holding the current selected category branch starting from nodeID to the top
     * @var array
     */
    protected $category_path = array();


    protected $breadcrumb = null;

    public $treeViewUseAgregateSelect = true;

    public function __construct()
    {
        parent::__construct();


        $this->sections = new SectionsBean();
        $this->section_filter = new GETVariableFilter("section", "section");

        $this->product_categories = new ProductCategoriesBean();
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
        $this->view->setItemsPerPage(12);

        $this->view->getTopPaginator()->view_modes_enabled = TRUE;

        $this->breadcrumb = new BreadcrumbList();


        $this->addCSS(STORE_LOCAL . "/css/product_list.css");
        $this->addJS(STORE_LOCAL . "/js/product_list.js");

    }

    protected function initSortFields()
    {
        $sort_price = new PaginatorSortField("sell_price", "Цена", "", "ASC");
        $this->view->getPaginator()->addSortField($sort_price);

        $sort_prod = new PaginatorSortField("prodID", "Най-нови", "", "DESC");
        $this->view->getPaginator()->addSortField($sort_prod);
    }

    /**
     * Post CTOR initialization
     */
    public function initialize()
    {
        //main products select - no grouping here as filters are not applied yet
        if (is_null($this->bean)) {
            throw new Exception("SellableProducts not set");
        }
        $this->select = clone $this->bean->select();

        $search_fields = array("product_name", "product_description", "long_description", "keywords");
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



    public function setSellableProducts(SellableProducts $bean)
    {
        $this->bean = $bean;
    }


    //process get vars
    public function processInput()
    {

        //filter precedence
        // 1 section
        // 2 keyword search
        // 3 category
        // 4 filters

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

            $this->select->where()->add("section", "'{$value}'");
        }

        $this->keyword_search->processInput();

        if ($this->keyword_search->isProcessed()) {
            $this->keyword_search->getForm()->prepareClauseCollection("AND")->copyTo($this->select->where());
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

        $this->select = $this->product_categories->selectChildNodesWith($this->select, "sellable_products", $nodeID, array("catID", "category_name"));

        if ($this->filters instanceof ProductListFilter) {

            //set initial products select. create attribute filters need to append the data inputs only.
            $this->filters->getForm()->setSQLSelect($this->select);
            $this->filters->getForm()->createAttributeFilters();
            //update here if all filter values needs to be visible
            //$this->filters->getForm()->updateIterators();

            //assign values from the query string to the data inputs
            $this->filters->processInput();

            $filters_where = $this->filters->getForm()->prepareClauseCollection(" AND ");
            //products list filtered
            $filters_where->copyTo($this->select->where());

            //tree view filtered
            $filters_where->copyTo($products_tree->where());
            //echo $this->select->getSQL()."<HR>";

            //filter values will be limited to the selection only
            //set again - rendering will use this final select
            $this->filters->getForm()->setSQLSelect($this->select);
            $this->filters->getForm()->updateIterators();

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

    public function renderProductsView()
    {

        $this->view->render();
    }

    protected function constructTitle()
    {
        if ($this->keyword_search->isProcessed()) {
            $this->setTitle("Резултати от търсене");
            return;
        }

        $title = array();

        if ($this->section) {
            $title[] = $this->section;
        }
        else {
            $title[] = tr($this->products_title);
        }

        foreach ($this->category_path as $idx => $catinfo) {
            $title[] = $catinfo["category_name"];
        }

        $this->setTitle(constructSiteTitle($title));
    }



    public function getCategoryPath()
    {
        return $this->category_path;
    }

    /**
     * Load the current selected category branch into the category_path array. Starting from nodeID to the top
     * @param int $nodeID
     */
    protected function loadCategoryPath(int $nodeID)
    {
        $category_path = $this->product_categories->getParentNodes($nodeID, array("catID", "category_name"));

        if ($category_path) {
            $this->category_path = $category_path;

            $length = count($category_path);
            if ($length>0) {
                $this->view->setName($category_path[$length-1]["category_name"]);
            }
        }
        else {
            $this->category_path = array();
        }
    }

    public function renderCategoryPath()
    {
        $actions = $this->constructPathActions();

        $this->breadcrumb->clear();
        foreach ($actions as $idx=>$cmp) {
            $this->breadcrumb->append($cmp);
        }

        $this->breadcrumb->render();
    }

    protected function constructPathActions() : array
    {
        $actions = array();

        $actions[] = new Action(tr("Начало"), LOCAL . "/home.php", array());

        $link = new URLBuilder();
        $link->buildFrom(LOCAL."/products/list.php");

        if ($this->section) {
            $link->add(new URLParameter("section", $this->section));
        }

        if ($this->keyword_search->isProcessed()) {
            $actions[] = new Action(tr("Резултати от търсене"), $this->getPageURL(), array());
        }
        else if ($this->section) {
            $actions[] = new Action($this->section, $link->url(), array());
        }
        else {
            $actions[] = new Action(tr($this->products_title), $link->url(), array());
        }

        $link->add(new DataParameter("catID"));

        foreach ($this->category_path as $idx => $category) {

            $link->setData($category);

            $category_action = new Action($category["category_name"], $link->url(), array());
            $category_action->translation_enabled = false;
            $actions[] = $category_action;

        }

        return $actions;
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

        $this->renderProductsView();

        echo "</div>";

        Session::set("shopping.list", $this->getPageURL());

    }
}

?>
