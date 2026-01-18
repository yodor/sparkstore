<?php
include_once("store/pages/ProductPageBase.php");

include_once("components/NestedSetTreeView.php");
include_once("components/renderers/items/TextTreeItem.php");
include_once("components/Action.php");
include_once("components/TableView.php");
include_once("components/ItemView.php");

include_once("components/ClosureComponent.php");

include_once("objects/SparkMap.php");
include_once("utils/GETProcessor.php");

include_once("store/components/ProductListFilter.php");
include_once("store/forms/ProductListFilterInputForm.php");

include_once("store/components/TogglePanel.php");

class ProductListPageBase extends ProductPageBase
{

    /**
     * @var NestedSetTreeView|null
     */
    protected ?NestedSetTreeView $treeView = NULL;

    /**
     * Used to render products/tree/filters
     * @var SQLSelect|null
     */
    protected ?SQLSelect $select = NULL;

    /**
     * Filters form component
     * @var ProductListFilter|null
     */
    protected ?ProductListFilter $filters = NULL;

    /**
     * Products list component
     * @var ItemView|null
     */
    protected ?ItemView $view = NULL;

    /**
     * Other get variable filters
     * @var SparkMap
     */
    protected SparkMap $property_filter;

    protected GETProcessor $category_filter;

    protected bool $treeViewAggregateSelect = true;
    protected bool $treeViewAggregateSelectCount = true;

    protected Container $aside;
    protected Container $main;

    public function __construct()
    {
        parent::__construct();

        $this->setSellableProducts($this->createSellableProducts());

        $this->property_filter = new SparkMap();

        $section_filter = new GETProcessor("section", "section");
        $closure = function(GETProcessor $filter) {
            $clause = new SQLClause();
            $value = $filter->getValue();
            $clause->setExpression("product_sections LIKE '%$value%'", "", "");
            $filter->getClauseCollection()->append($clause);
        };
        $section_filter->setClosure($closure);
        $this->property_filter->set($section_filter->getName(), $section_filter);

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
        $this->filters = $this->createProductFilters();


        $this->view = new ItemView();
        $this->view->setCacheable(true);
        
        $this->view->setItemRenderer(new ProductListItem());
        $this->view->setItemsPerPage(12);

        //disable list/grid
        $this->view->getHeader()->getViewMode()->setRenderEnabled(false);

        $this->head()->addCSS(STORE_LOCAL . "/css/product_list.css");
        $this->head()->addJS(STORE_LOCAL . "/js/product_list.js");

        //enable canonical link tag
        $categoryURL = new CategoryURL();
        $categoryParameters = $categoryURL->getParameterNames();
        $this->head()->addCanonicalParameter(...$categoryParameters);



    }

    protected function createProductFilters() : ?ProductListFilter
    {
        $filters = new ProductListFilter(new ProductListFilterInputForm());
        //$filters->getSubmitButton()->setRenderEnabled(false);
        return $filters;
    }

    protected function headFinalize() : void
    {
        parent::headFinalize();

        AbstractResultView::AppendHeadLinks($this->view, $this);
    }

    protected function createSellableProducts() : SellableProducts
    {
        return new SellableProducts();
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
    protected function initSortFields(): void
    {
        $sort_prod = new OrderColumn("prodID", "Най-нови",  "DESC");
        $this->view->getPaginator()->addOrderColumn($sort_prod);

        $sort_price = new OrderColumn("sell_price", "Цена", "ASC");
        $this->view->getPaginator()->addOrderColumn($sort_price);
    }


    /**
     * Post-constructor initialization
     * @return void
     * @throws Exception
     */
    public function initialize() : void
    {

        $this->initPrivate();

        //main products select - no grouping here as filters are not applied yet
        if (is_null($this->bean)) {
            throw new Exception("List bean is not set");
        }
        $this->select = clone $this->bean->select();
        $this->select->fields()->setPrefix("sellable_products");


        $search_fields = array("product_name");
        $this->keyword_search->getForm()->setColumns($search_fields);

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

    public function getProcessors() : SparkMap
    {
        return $this->property_filter;
    }

    /**
     * Process the page input
     * Calls prepare Keywords and prepare Description to set the values of meta-keywords and meta-description
     * @return void
     * @throws Exception
     */
    public function processInput() : void
    {

        //filter precedence
        // 0 property filters
        // 3 keyword search
        // 5 attribute filters

        $columnsCopy = clone $this->select->fields();

        $iterator = $this->property_filter->iterator();
        while ($filter = $iterator->next()) {
            if (!($filter instanceof GETProcessor)) continue;
            //skip already processed filters in parent
            if ($filter->isProcessed()) continue;
            $filter->setSQLSelect($this->select);
            $filter->processInput();
        }
        //
        $this->processFilters();

        $section_filter = $this->property_filter->get("section");
        if ($section_filter instanceof GETProcessor && $section_filter->isProcessed()) {
            $this->setSection($section_filter->getValue());
            $colums = array();
            if ($this->sections->haveColumn("section_seodescription")) {
                $columns[] = "section_seodescription";
            }
            $result = $this->sections->getResult("section_title", $section_filter->getValue(), ...$columns);
            if (isset($result["section_seodescription"]) && $result["section_seodescription"]) {
                $this->setMetaDescription($result["section_seodescription"]);
            }

        }

        $this->category_filter->processInput();

        if ($this->category_filter->isProcessed()) {
            $this->treeView->setSelectedID(intval($this->category_filter->getValue()));
        }


        $this->keyword_search->processInput();

        if ($this->keyword_search->isProcessed()) {
            $cc = $this->keyword_search->getForm()->prepareClauseCollection("AND");
            $cc->copyTo($this->select->where());
        }


        $this->view->setName(tr("All Products"));

        $nodeID = $this->treeView->getSelectedID();
        if ($nodeID > 0) {
            $this->loadCategoryPath($nodeID);
            $length = count($this->category_path);
            if ($length>0) {
                $this->view->setName($this->category_path[$length-1]["category_name"]);
            }

        }

        //clone the main products select here to keep the tree siblings visible
        $products_tree = clone $this->select;

        if ($nodeID>0) {
            //unset - will use catID and category name from selectChildNodesWith
            $this->select->fields()->unset("catID");
            $this->select->fields()->unset("category_name");
            $this->select = $this->product_categories->selectChildNodesWith($this->select, $this->bean->getTableName(), $nodeID, array("catID", "category_name"));
        }

//        $this->processFilters()

//echo $this->select->getSQL();

        //setup grouping for the list item view
//        $this->select->group_by = SellableProducts::DefaultGrouping();
//        echo $this->select->getSQL();
        //primary key is prodID as we group by prodID(Products) not piID(ProductInventory)
        $this->view->setIterator(new SQLQuery($this->select, "prodID"));

        //construct category tree for the products that will be listed
        //keep same grouping as the products list
        $products_tree->group_by = $this->select->group_by;

        //do not clear the fields here as filters might have appended dynamic columns
        //select only fields needed in the treeView iterator
        //$products_tree->fields()->reset();

        //Remove non-needed columns.
        //Filters append having clause too so let dynamic columns in
        foreach ($columnsCopy->names() as $name) {
            $products_tree->fields()->unset($name);
        }
        $products_tree->fields()->set("sellable_products.prodID");
        $products_tree->fields()->set("sellable_products.catID");

        //echo $products_tree->getSQL();

        $products_tree = $products_tree->getAsDerived();
        $products_tree->fields()->set("relation.prodID", "relation.catID");

        //needs getAsDerived - sets grouping and ordering on the returned select, suitable as treeView iterator
        $aggregateSelect = $this->product_categories->selectTreeRelation($products_tree, "relation", "prodID", array("category_name"), $this->treeViewAggregateSelectCount);

        if ($this->treeViewAggregateSelect) {
            $this->treeView->setIterator(new SQLQuery($aggregateSelect, $this->product_categories->key()));
        }

        $this->processTreeViewURL();


    }

    public function setSection(string $section) : void
    {
        $this->section = $section;
        $this->aside->setAttribute("section", $this->section);

    }
    /**
     * Return the active selected section title
     * @return string
     */
    public function getSection() : string
    {
        return $this->section;
    }

    protected function processFilters() : void
    {
        if ($this->filters instanceof ProductListFilter) {

            $filtersForm = $this->filters->getForm();
            if ($filtersForm instanceof ProductListFilterInputForm) {
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

                //tree view filtered already set because filters are processed before products_tree select clone is created
                //$filters_where->copyTo($products_tree->where());


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
        }
    }

    public function getParameterNames() : array
    {
        $supported_params = array();

        $categoryURL = new CategoryURL();
        $categoryParameters = $categoryURL->getParameterNames();
        foreach ($categoryParameters as $name) {
            $supported_params[] = $name;
        }

        //append property filter names
        $iterator = $this->property_filter->iterator();
        while ($filter = $iterator->next()) {
            if (!($filter instanceof GETProcessor)) continue;
            $supported_params[] = $filter->getName();
        }

        //append dynamic filter names
        if ($this->filters) {
            foreach ($this->filters->getForm()->inputNames() as $idx => $name) {
                $supported_params[] = $name;
            }
        }

        //keyword search
        if ($this->keyword_search && $this->keyword_search->isProcessed()) {
            foreach ($this->keyword_search->getForm()->inputNames() as $idx => $name) {
                $supported_params[] = $name;
            }
            $supported_params[] = KeywordSearch::SUBMIT_KEY;
        }

        //remove key page
//        $view_params = $this->view->getPaginator()->getParameterNames();
//        foreach ($view_params as $idx=>$name) {
//            if (str_contains($name, Paginator::KEY_PAGE))continue;
//            $supported_params[] = $name;
//        }
        return $supported_params;
    }
    /**
     * Set clean URL for tree view items
     * Only transfer parameters that affect the query of the treeview
     * @return void
     */
    protected function processTreeViewURL() : void
    {

        $supported_params = $this->getParameterNames();

        $item = $this->treeView->getItemRenderer();
        if ($item instanceof TextTreeItem) {

            $pageURL = URL::Current();

            URL::Clean($pageURL, $supported_params);

            $itemURL = new CategoryURL($pageURL);
            $item->getTextAction()->setURL($itemURL);

        }

    }

    //return current selected category page url or the main products list
    public function currentURL() : URL
    {

        $url = parent::currentURL();

        $nodeID = $this->treeView->getSelectedID();
        if ($nodeID>0) {
            $url = new CategoryURL($url);
            $url->setData(array("catID" => $nodeID, "category_name"=>$this->view->getName()));
        }

        return $url;
    }

    public function isProcessed(): bool
    {
        return $this->keyword_search->isProcessed();
    }

    public function renderProductFilters(): void
    {



    }

    protected function renderActiveFilterValues() : void
    {
        if ($this->filters instanceof ProductListFilter) {
            $active_filters = $this->filters->getActiveFilters();
            if (count($active_filters) > 0) {
                echo "<div class='active_filters panel'>";

                $active_filters = $this->filters->getActiveFilters();
                $viewCaption = "";
                foreach ($active_filters as $flabel => $fvalue) {
                    $viewCaption .= tr($flabel) . ": " . $fvalue . "; ";
                }
                echo "<div class='Caption'>" . $viewCaption . "</div>";

                echo "</div>";
            }
        }
    }

    /**
     * Renders list of sub-categories with image or all categories if no category is selected
     * @return void
     * @throws Exception
     */
    public function renderChildCategories(): void
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
        $builder =  new CategoryURL();
        $si = new StorageItem();
        $si->className = "ProductCategoryPhotosBean";
        while ($result = $query->nextResult()) {
            $builder->setData($result->toArray());
            $si->id = $result->get("pcpID");
            $si->setName($result->get("category_name"));
            echo "<div class='item'>";
            echo "<a href='{$builder->toString()}' title='{$result->get("category_name")}'><img src='{$si->hrefCrop(128,-1)}' alt='{$result->get("category_name")}'>{$result->get("category_name")}</a>";
            echo "</div>";
        }
        echo "</div>";

        
    }






    protected function renderListHeader() : void
    {
        $catID = $this->treeView->getSelectedID();

        $cmp = new Component();
        $cmp->setTagName("h1");
        $cmp->setComponentClass("Caption");
        $cmp->setClassName("category_name");
        $cmp->setContents($this->getTitle());
        $cmp->render();

        if ($this->description) {
            echo "<h2 class='Caption category_description seo_description'>{$this->description}</h2>";
        }

    }

    protected function renderListFooter() : void
    {
        $catID = $this->treeView->getSelectedID();

        $category_description="";
        if ($catID>0 && $this->product_categories->haveColumn("category_description")) {
            $category_description = $this->product_categories->getValue($catID, "category_description");
        }

        if (!$category_description) {
            $config = ConfigBean::Factory();
            $config->setSection("store_config");
            $category_description = $config->get("product_list_footer");
        }

        if ($category_description) {
            echo "<section class='category_description'>";
            echo $category_description;
            echo "</section>";
        }
    }

    /**
     * Inner page layout initialization
     * @return void
     */
    private function initPrivate(): void
    {
        $cmp = new ClosureComponent($this->renderCategoryPath(...), false, false);
        $this->items()->append($cmp);

        $aside = new Container(false);
        $aside->setTagName("aside");
        $aside->setAttribute("section", $this->section);
        $aside->setComponentClass("column");
        $aside->addClassName("left");

        $panel = new TogglePanel();
        $panel->addClassName("categories");
        $panel->setName("categories");
        $panel->setTitle("Категории");
        $panel->getViewport()->items()->append($this->treeView);
        $aside->items()->append($panel);



        if ($this->filters instanceof ProductListFilter) {
            $panel = new TogglePanel();
            $panel->addClassName("filters");
            $panel->setName("filters");
            $panel->setTitle($this->filters->getTitle());
            $panel->getViewport()->items()->append($this->filters);
            $addOn = new ClosureComponent($this->renderActiveFilterValues(...), false, false);
            $panel->items()->append($addOn);
            $aside->items()->append($panel);
        }

        $this->items()->append($aside);
        $this->aside = $aside;

        $main = new Container(false);
        $main->setTagName("main");
        $main->setComponentClass("column");
        $main->addClassName("products_list");

        $cmp = new ClosureComponent($this->renderListHeader(...), false, false);
        $main->items()->append($cmp);

        $cmp = new ClosureComponent($this->renderChildCategories(...), false, false);
        $main->items()->append($cmp);

        //view
        $main->items()->append($this->view);

        $cmp = new ClosureComponent($this->renderListFooter(...), false, false);
        $main->items()->append($cmp);

        $this->items()->append($main);
        $this->main = $main;


        Session::set("shopping.list", $this->currentURL());

    }

}

?>
