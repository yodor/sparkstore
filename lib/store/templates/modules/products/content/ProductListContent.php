<?php
include_once("utils/script/Marketing.php");
include_once("utils/ImageSizes.php");
include_once("store/templates/modules/products/content/ProductContent.php");
include_once("store/templates/modules/products/content/ProductListConfig.php");
include_once("store/beans/SellableProducts.php");
include_once("store/beans/SectionsBean.php");
include_once("store/beans/ProductViewLogBean.php");
include_once("store/utils/url/CategoryURL.php");
include_once("store/utils/url/ProductListURL.php");
include_once("store/utils/url/ProductSearchURL.php");
include_once("store/components/renderers/items/ProductListItem.php");
include_once("store/components/ProductListFilter.php");
include_once("store/forms/ProductListFilterInputForm.php");
include_once("store/components/TogglePanel.php");
include_once("components/NestedSetTreeView.php");
include_once("components/renderers/items/TextTreeItem.php");
include_once("components/Action.php");
include_once("components/ItemView.php");
include_once("components/ClosureComponent.php");
include_once("components/Container.php");
include_once("storage/StorageItem.php");
include_once("objects/OrderColumn.php");
include_once("components/KeywordSearch.php");
include_once("objects/SparkMap.php");
include_once("objects/data/LinkedData.php");
include_once("utils/GETProcessor.php");
include_once("utils/script/LDJsonScript.php");
include_once("objects/data/GTMConvParam.php");

/**
 * The products list (products module paths 'home', 'category', 'promos', 'search' - ProductListConfig::$list):
 * the breadcrumb, the categories tree and the filters (aside), the list of the products (main). The categories of
 * the list at /products/category/<catID>/<slug>/ (promos: /products/promos/<catID>/<slug>/, search:
 * /products/search/<catID>/<slug>/) - one address per category (301 to the current slug).
 * A keyword search on the list or a category goes to the search address (301); the search without a keyword
 * goes back to the list (301). The search is not indexed.
 *
 * Filter precedence: the property filters (section ...), the attribute filters (createProductFilters()), the
 * category, the keyword search.
 *
 * A site subclasses it in its products content folder and names it in its path files home.php, category.php,
 * promos.php, search.php:
 *  - initialize() - parent first, then its item renderer, its property filters, the panels order
 *  - createProductFilters() - its attribute filters (the 'Filters' panel)
 *  - listTitleDescription() - its title rule
 *  - initSortFields(), renderChildCategories(), renderListHeader(), renderListFooter()
 */
class ProductListContent extends ProductContent
{
    protected ?SellableProducts $bean = null;

    protected ?SectionsBean $sections = null;

    /**
     * The selected section (property filters 'section' / a site's own)
     * @var string
     */
    protected string $section = "";

    protected ?KeywordSearch $keyword_search = null;

    /**
     * The attribute filters (the 'Filters' panel) - none by default
     * @var ProductListFilter|null
     */
    protected ?ProductListFilter $filters = null;

    /**
     * The filters by GET parameters ('section', a site adds its own)
     * @var SparkMap|null
     */
    protected ?SparkMap $property_filter = null;

    protected ?GETProcessor $category_filter = null;

    protected ?NestedSetTreeView $treeView = null;

    /**
     * The products select of the list, the tree and the filters
     * @var SQLSelect|null
     */
    protected ?SQLSelect $select = null;

    /**
     * The list of the products
     * @var ItemView|null
     */
    protected ?ItemView $view = null;

    protected bool $treeViewAggregateSelect = true;

    /**
     * Show the number of products next to each category of the tree. Costly on large catalogs: the tree
     * query then carries one row per product (30K products: about 0.16 s instead of 0.01 s) - keep the
     * component cache of the tree enabled (setCacheable) when this is on.
     * @var bool
     */
    protected bool $treeViewAggregateSelectCount = true;

    protected ?Container $aside = null;
    protected ?Container $main = null;
    protected ?ActiveFilterComponent $filtersList = null;

    /**
     * Old GET parameter names => current ones (old links)
     * @var array
     */
    protected array $remapGetParameters = array();

    /**
     * The address of this list without a category: /products/, /products/promos/, /products/search/
     * @var string
     */
    protected string $listAddress = "";

    public static function ConfigClass() : string
    {
        return ProductListConfig::class;
    }

    /**
     * The config of a products list path file
     * @param string $list ProductListConfig::LIST_ALL, LIST_PROMOS, LIST_SEARCH
     * @return ProductListConfig
     */
    public static function Configure(string $list = ProductListConfig::LIST_ALL) : TemplateConfig
    {
        $config = parent::Configure();
        if ($config instanceof ProductListConfig) {
            $config->list = $list;
            $config->requireAuth = false;
        }
        return $config;
    }

    public function listType() : string
    {
        return ($this->config instanceof ProductListConfig) ? $this->config->list : ProductListConfig::LIST_ALL;
    }

    public function initialize() : void
    {
        $page = SparkPage::Instance();
        $this->cmp = new Container(false);

        //the category addresses of this list
        $list = $this->listType();
        if ($list === ProductListConfig::LIST_PROMOS) {
            CategoryURL::$urlCategorySlug = "/products/promos/";
            $this->listAddress = Spark::Get(Config::LOCAL) . "/products/promos/";
        }
        else if ($list === ProductListConfig::LIST_SEARCH) {
            CategoryURL::$urlCategorySlug = ProductSearchURL::$urlProductSearch;
            $this->listAddress = (new ProductSearchURL())->toString();
            $page?->head()->addMeta("robots", "noindex, follow");
        }

        //the body classes of the former page classes - the site css
        $page?->body()->addClassName("ProductListPage");
        if ($list === ProductListConfig::LIST_PROMOS) $page?->body()->addClassName("ProductPromoListPage");
        if ($list === ProductListConfig::LIST_SEARCH) $page?->body()->addClassName("ProductSearchListPage");

        new ProductViewLogBean();
        $this->sections = new SectionsBean();

        //the keyword search of the store frame (header)
        $this->keyword_search = ($page instanceof StorePage) ? $page->getKeywordSearch() : null;
        if (!($this->keyword_search instanceof KeywordSearch)) $this->keyword_search = new KeywordSearch();

        //the canonical address keeps the page from the second page on (?page=2 ...) - page 1 has none
        if ((int)($_GET[Paginator::KEY_PAGE] ?? 0) > 1) {
            $page?->head()->addCanonicalParameter(Paginator::KEY_PAGE);
        }
        $page?->head()->addCanonicalParameter(...(new CategoryURL())->getParameterNames());

        $conversion = Marketing::Conversion(GTMConvParam::VIEW_PLP);
        if ($conversion) $page?->head()->addScript($conversion);

        $this->bean = $this->createSellableProducts();
        if ($list === ProductListConfig::LIST_PROMOS) {
            $clause = new SQLClause();
            $clause->setExpression("(discount_percent > 0 OR promo_price > 0)");
            $this->bean->select()->where()->append($clause);
        }

        $this->property_filter = new SparkMap();
        $section_filter = new GETProcessor("section", "section");
        $section_filter->setClosure(function(GETProcessor $filter) {
            $filter->setTitle($filter->getValue());
            $clause = new SQLClause();
            $value = $filter->getValue();
            $clause->setExpression("product_sections LIKE :section_name");
            $clause->bind(":section_name", "%$value%");
            $filter->getClauseCollection()->append($clause);
        });
        $this->property_filter->set($section_filter->getName(), $section_filter);

        $this->category_filter = new GETProcessor("catID", "catID");

        //the categories tree - the links are set in processTreeViewURL()
        $this->treeView = new NestedSetTreeView();
        //component cache: cheap without product counts, needed with them ($treeViewAggregateSelectCount)
        $this->treeView->setCacheable(true);
        $this->treeView->setName("products_tree");
        $ir = new TextTreeItem();
        $ir->setLabelKey("category_name");
        $ir->getTextAction()->addDataAttribute("title", "category_name");
        $this->treeView->setItemRenderer($ir);

        $this->filters = $this->createProductFilters();

        $this->view = new ItemView();
        //component cache: measured on the live bookshop the cached list renders in about half the time
        //(0.09 s instead of 0.17-0.21 s). Prices and stock in lists can be up to PAGE_CACHE_TTL old -
        //the product page itself is not cached.
        $this->view->setCacheable(true);
        $this->view->setItemRenderer(new ProductListItem());
        $this->view->setItemsPerPage(12);
        $this->view->setName(tr("All Products"));
        //no list / grid switch
        $this->view->getHeader()->getViewMode()->setRenderEnabled(false);

        $this->filtersList = new ActiveFilterComponent();
        $this->filtersList->setRenderEnabled(false);
        $this->view->items()->insert($this->filtersList, 0);

        $this->view->setSchemaType($this->view->getSchemaType() . " https://schema.org/OfferCatalog");

        $page?->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/product_list.css");
        $page?->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL) . "/js/product_list.js");

        $this->initLayout();

        //the products select - no grouping here, the filters are not applied yet
        $this->select = clone $this->bean->select();
        $this->select->columns()->setPrefix("sellable_products");

        $this->keyword_search->getForm()->setColumns(array("product_name", "attribute_value"));

        //default - all categories, not filtered or aggregated
        $treeSelect = $this->product_categories->selectTree(array("category_name"));
        $this->treeView->setIterator(new SelectQuery($treeSelect, $this->product_categories->key(), $this->product_categories->table()));

        //default - all products of all categories
        $this->view->setIterator(new SelectQuery(clone $this->select, "prodID"));

        $this->initSortFields();

        $this->section = "";
    }

    /**
     * The breadcrumb, the aside column (categories, filters), the main column (header, sub categories, the list,
     * footer)
     * @return void
     */
    protected function initLayout() : void
    {
        $this->cmp->items()->append($this->breadcrumb);

        $aside = new Container(false);
        $aside->setTagName("aside");
        $aside->setAttribute("section", $this->section);
        $aside->setComponentClass("column");
        $aside->addClassName("left");

        $panel = new TogglePanel();
        $panel->addClassName("categories");
        $panel->setName("categories");
        $panel->setTitle(tr("Categories"));
        $panel->getViewport()->items()->append($this->treeView);
        $aside->items()->append($panel);

        if ($this->filters instanceof ProductListFilter) {
            $panel = new TogglePanel();
            $panel->addClassName("filters");
            $panel->setName("filters");
            $panel->setTitle(tr("Filters"));
            $panel->getViewport()->items()->append($this->filters);
            $aside->items()->append($panel);
        }

        $this->cmp->items()->append($aside);
        $this->aside = $aside;

        $main = new Container(false);
        $main->setTagName("main");
        $main->setComponentClass("column");
        $main->addClassName("products_list");

        $main->items()->append(new ClosureComponent($this->renderListHeader(...), false, false));
        $main->items()->append(new ClosureComponent($this->renderChildCategories(...), false, false));
        $main->items()->append($this->view);
        $main->items()->append(new ClosureComponent($this->renderListFooter(...), false, false));

        $this->cmp->items()->append($main);
        $this->main = $main;
    }

    protected function createSellableProducts() : SellableProducts
    {
        return new SellableProducts();
    }

    /**
     * The attribute filters of the list - a site returns its own (the 'Filters' panel)
     * @return ProductListFilter|null
     */
    protected function createProductFilters() : ?ProductListFilter
    {
        return null;
    }

    /**
     * The sorting of the list
     * @return void
     */
    protected function initSortFields() : void
    {
        $this->view->getPaginator()->addOrderColumn(OrderColumn::Labeled("prodID", "Newest", OrderDirection::DESC));
        $this->view->getPaginator()->addOrderColumn(OrderColumn::Labeled("sell_price", "Price", OrderDirection::ASC));
    }

    /**
     * The filters, the category, the keyword search; the list and the tree queries; the breadcrumb, the title
     * @return void
     * @throws Exception
     */
    public function processInput() : void
    {
        parent::processInput();

        //old parameters (remapGetParameters)
        foreach ($this->remapGetParameters as $key => $value) {
            if (isset($_GET[$key])) {
                $_GET[$value] = $_GET[$key];
                unset($_GET[$key]);
            }
        }

        $columnNamesCopy = $this->select->columns()->names();

        $iterator = $this->property_filter->iterator();
        while ($filter = $iterator->next()) {
            if (!($filter instanceof GETProcessor)) continue;
            if ($filter->isProcessed()) continue;
            $filter->setSQLSelect($this->select);
            $filter->processInput();
        }

        $this->processFilters();

        $section_filter = $this->property_filter->get("section");
        if ($section_filter instanceof GETProcessor && $section_filter->isProcessed()) {
            $this->setSection($section_filter->getValue());
            $filter = new ActiveFilterItem(array($section_filter->getName()), tr("Section"), $section_filter->getValue());
            $this->filtersList->filter()->items()->append($filter);
        }

        $this->category_filter->processInput();
        if ($this->category_filter->isProcessed()) {
            $this->treeView->setSelectedID(intval($this->category_filter->getValue()));
        }

        $this->keyword_search->processInput();

        $list = $this->listType();
        //a search on the list or a category goes to the search address (not indexed)
        if ($this->keyword_search->isProcessed() && $list === ProductListConfig::LIST_ALL) {
            $this->redirectSearch();
        }
        //the search address without a keyword: the same list without the search
        if (!$this->keyword_search->isProcessed() && $list === ProductListConfig::LIST_SEARCH) {
            $this->redirectNoSearch();
        }

        if ($this->keyword_search->isProcessed()) {
            $keyword = $this->keyword_search->getForm()->getInput("keyword")->getValue();
            $cc = $this->keyword_search->getForm()->prepareClauseCollection("OR");
            $cc->copyTo($this->select->where());
            $this->select->from()->leftJoin("product_attributes pa")->on("pa.prodID = sellable_products.prodID");
            $this->select->group_by = " sellable_products.prodID ";
            $filter = new ActiveFilterItem(array("filter", "keyword"), tr("Search for"), $keyword);
            $this->filtersList->filter()->items()->append($filter);
        }

        //the products
        $products_list = clone $this->select;
        $nodeID = $this->treeView->getSelectedID();
        if ($nodeID > 0) {
            //lateLookup on child.catID (selectChildNodesWith) and the bean table.key
            $products_list = $this->product_categories->selectChildNodesWith($products_list, $this->bean->table(), $nodeID, []);
        }
        $query = new SelectQuery($products_list, $this->bean->key(), $this->bean->table());
        $query->execLateLookup = true;
        $this->view->setIterator($query);

        //the tree - keep the columns the filters added (having clauses), drop the columns of the list
        $products_tree = clone $this->select;
        foreach ($columnNamesCopy as $name) {
            $products_tree->columns()->unset($name);
        }
        $products_tree->alias("DISTINCT sellable_products.catID", "catID");
        //the products only when the tree shows their counts - otherwise DISTINCT catID keeps the derived
        //table at one row per category instead of one per product (30K products: 0.16 s -> 0.01 s)
        if ($this->treeViewAggregateSelectCount) {
            $products_tree->column("sellable_products.prodID");
        }
        $products_tree = $products_tree->getAsDerived("relation");
        $aggregateSelect = $this->product_categories->selectTreeRelation($products_tree, "relation", "prodID", array("category_name"), $this->treeViewAggregateSelectCount);
        if ($this->treeViewAggregateSelect) {
            $this->treeView->setIterator(new SelectQuery($aggregateSelect, $this->product_categories->key()));
        }

        if ($nodeID > 0) {
            $this->loadCategoryPath($nodeID);
            if (count($this->category_path) > 0) {
                $this->view->setName(array_reverse($this->category_path)[0]["category_name"]);
                //one address per category: /products/category/<catID>/<current name slug>/ - others get a 301
                Module::RedirectCanonical($this->currentURL());
            }
        }

        $this->processTreeViewURL();

        if ($this->filters instanceof ProductListFilter) {
            if ($this->filtersList->filter()->items()->count() > 0) {
                $this->filtersList->setRenderEnabled(true);
            }
        }

        $this->fillBreadCrumb();

        $page = SparkPage::Instance();
        $page?->setContentURL($this->currentURL());

        [$title, $description] = $this->listTitleDescription();
        if (mb_strlen($title) > 0) $this->config->title = $title;
        if (mb_strlen($description) > 0) $page?->setMetaDescription($description);

        //the cart 'continue shopping' comes back here
        Session::SetIfActive("shopping.list", $this->currentURL()->toString());
    }

    protected function processFilters() : void
    {
        if ($this->filters instanceof ProductListFilter) {
            $this->filters->setSQLSelect($this->select);
            $this->filters->processInput();
            foreach ($this->filters->getActiveFilters() as $filter) {
                $this->filtersList->filter()->items()->append($filter);
            }
        }
    }

    public function setSection(string $section) : void
    {
        $this->section = $section;
        $this->aside?->setAttribute("section", $this->section);
    }

    /**
     * The selected section title
     * @return string
     */
    public function getSection() : string
    {
        return $this->section;
    }

    public function getProcessors() : SparkMap
    {
        return $this->property_filter;
    }

    public function getSellableProducts() : SellableProducts
    {
        return $this->bean;
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
     * Title (the <title>) and description of the list: the category branch (SEO title / name, SEO description /
     * description), the section before it (its SEO title / name, its SEO description), the search before them -
     * [title, description]; an empty title leaves it to the frame (the selected menu item), then 'All Products'
     * (fallbackTitle())
     * @return array
     * @throws Exception
     */
    protected function listTitleDescription() : array
    {
        [$title, $description] = $this->categoryTitleDescription();

        if ($this->section) {
            $section = $this->sectionRow();
            $description = SEOData::Description($section, $description);
            $title = $this->joinTitle(SEOData::Title($section, $this->section), $title);
        }

        return array($this->searchTitle($title), $description);
    }

    /**
     * The heading of the list (its h1): the names - the category, the section before it, the search before them.
     * The SEO titles go only to the <title> (listTitleDescription). Empty: the title of the page.
     * @return string
     */
    protected function listHeading() : string
    {
        $heading = $this->categoryName();
        if ($this->section) {
            $heading = $this->joinTitle($this->section, $heading);
        }
        return $this->searchTitle($heading);
    }

    /**
     * The row of the selected section: its SEO title and description
     * @return array
     * @throws Exception
     */
    protected function sectionRow() : array
    {
        $result = $this->sections->getResult("section_title", $this->section, SEOInputGroup::TITLE, SEOInputGroup::DESCRIPTION);
        return is_array($result) ? $result : array();
    }

    /**
     * '$first - $rest' ('$first' alone when '$rest' is empty)
     */
    protected function joinTitle(string $first, string $rest) : string
    {
        return $first . ($rest !== "" ? " - " . $rest : "");
    }

    /**
     * '$title' after the search: 'Search results - <keyword> - $title', 'Search results - $title' for the filters
     */
    protected function searchTitle(string $title) : string
    {
        if ($this->keyword_search->isProcessed()) {
            $search_value = (string)$this->keyword_search->getForm()->getInput("keyword")->getValue();
            return $this->joinTitle(tr("Search results") . " - " . $search_value, $title);
        }
        if ($this->filters instanceof ProductListFilter && count($this->filters->getActiveFilters()) > 0) {
            return $this->joinTitle(tr("Search results"), $title);
        }
        return $title;
    }

    public function fallbackTitle() : string
    {
        return tr("All Products");
    }

    /**
     * The breadcrumb: the search or the filters (else the home action of the frame), the property filters,
     * the categories
     * @return array<Action>
     */
    protected function constructPathActions() : array
    {
        $actions = array();
        $link = new ProductListURL();

        if ($this->keyword_search->isProcessed()) {
            $link = new ProductSearchURL();
            $current = URL::Current();
            foreach ($this->keyword_search->getForm()->inputNames() as $name) {
                if ($current->contains($name)) {
                    $link->add($current->get($name));
                }
            }
            if ($current->contains(KeywordSearch::SUBMIT_KEY)) {
                $link->add($current->get(KeywordSearch::SUBMIT_KEY));
            }
            $search_title = tr("Search results") . ": " . $this->keyword_search->getForm()->getInput("keyword")->getValue();
            $actions[] = new Action($search_title, $link, array());
        }
        else if ($this->filters instanceof ProductListFilter && count($this->filters->getActiveFilters()) > 0) {
            $link = URL::Current();
            $link->remove("catID");
            $actions[] = new Action(tr("Search results"), $link->toString(), array());
        }
        else {
            $page = SparkPage::Instance();
            if ($page instanceof StorePage && $page->getHomeAction() instanceof Action) {
                $actions[] = $page->getHomeAction();
            }
        }

        $iterator = $this->property_filter->iterator();
        while ($filter = $iterator->next()) {
            if ($filter instanceof GETProcessor && $filter->isProcessed()) {
                $link->add(new DataParameter($filter->getName(), urlencode($filter->getValue())));
                $actions[] = new Action($filter->getTitle(), $link->toString(), array());
            }
        }

        foreach ($this->category_path as $category) {
            $link = new CategoryURL($link);
            $link->setData($category);
            $actions[] = new Action($category["category_name"], $link->toString(), array());
        }

        return $actions;
    }

    /**
     * 301 to the same search at the search address: /products/ → /products/search/,
     * /products/category/<catID>/<slug>/ → /products/search/<catID>/<slug>/ - the query stays
     * @return never
     */
    protected function redirectSearch() : never
    {
        $current = URL::Requested();
        $path = $current->getScriptName();
        $products = Spark::Get(Config::LOCAL) . ProductListURL::$urlProductList;
        $rest = str_starts_with($path, $products) ? substr($path, strlen($products)) : "";
        $rest = str_starts_with($rest, "category/") ? substr($rest, strlen("category/")) : "";

        $url = new URL((new ProductSearchURL())->toString() . $rest);
        $url->copyParametersFrom($current);
        header("Location: " . $url->toString(), true, 301);
        exit;
    }

    /**
     * 301 from the search address without a keyword to the same list without the search:
     * /products/search/ → /products/, /products/search/<catID>/<slug>/ → /products/category/<catID>/<slug>/
     * @return never
     */
    protected function redirectNoSearch() : never
    {
        $current = URL::Requested();
        $path = $current->getScriptName();
        $search = Spark::Get(Config::LOCAL) . ProductSearchURL::$urlProductSearch;
        $rest = str_starts_with($path, $search) ? substr($path, strlen($search)) : "";
        $target = (preg_match("#^[0-9]+/#", $rest)) ? Spark::Get(Config::LOCAL) . "/products/category/" . $rest : (new ProductListURL())->toString();

        $url = new URL($target);
        $url->copyParametersFrom($current);
        $url->remove(KeywordSearch::SUBMIT_KEY);
        foreach ($this->keyword_search->getForm()->inputNames() as $name) {
            $url->remove($name);
        }
        header("Location: " . $url->toString(), true, 301);
        exit;
    }

    /**
     * The parameters of the list (category, property filters, attribute filters, the keyword search)
     * @return array
     */
    public function getParameterNames() : array
    {
        $supported_params = array();

        foreach ((new CategoryURL())->getParameterNames() as $name) {
            $supported_params[] = $name;
        }

        $iterator = $this->property_filter->iterator();
        while ($filter = $iterator->next()) {
            if (!($filter instanceof GETProcessor)) continue;
            $supported_params[] = $filter->getName();
        }

        if ($this->filters instanceof ProductListFilter) {
            foreach ($this->filters->getFilters()->keys() as $name) {
                $supported_params[] = $name;
            }
        }

        if ($this->keyword_search->isProcessed()) {
            foreach ($this->keyword_search->getForm()->inputNames() as $name) {
                $supported_params[] = $name;
            }
            $supported_params[] = KeywordSearch::SUBMIT_KEY;
        }

        return $supported_params;
    }

    /**
     * The links of the tree items: only the parameters that affect the query of the tree
     * @return void
     */
    protected function processTreeViewURL() : void
    {
        $item = $this->treeView->getItemRenderer();
        if ($item instanceof TextTreeItem) {
            $pageURL = URL::Current();
            URL::Clean($pageURL, $this->getParameterNames());
            $item->getTextAction()->setURL(new CategoryURL($pageURL));
        }
    }

    /**
     * The address of the list: the selected category or the list address, with the query of the request
     * @return URL
     */
    public function currentURL() : URL
    {
        $current = URL::Requested();
        $current->remove("path");
        $url = new URL(($this->listAddress !== "") ? $this->listAddress : (new ProductListURL())->toString());
        $url->copyParametersFrom($current);

        $nodeID = $this->treeView->getSelectedID();
        if ($nodeID > 0) {
            $url = new CategoryURL($url);
            $url->setData(array("catID" => $nodeID, "category_name" => $this->view->getName()));
        }
        return $url;
    }

    /**
     * The sub categories (with their photo) of the selected category, or the top categories. No site shows them
     * yet (bookshop and mobilevrati override this with nothing) - the default of a store that wants them.
     * @return void
     * @throws Exception
     */
    public function renderChildCategories() : void
    {
        $parentID = max(0, $this->treeView->getSelectedID());

        $query = $this->product_categories->query($this->product_categories->key(), "category_name");
        $query->stmt->alias("(SELECT pcp.pcpID FROM product_category_photos pcp WHERE pcp.catID = product_categories.catID ORDER BY pcp.position ASC LIMIT 1)", "pcpID");
        $query->stmt->where()->match("parentID", $parentID);
        $query->stmt->order("lft", OrderDirection::ASC);
        $query->exec();

        $list = new Container(false);
        $list->setComponentClass("category_list");

        $builder = new CategoryURL();
        while ($result = $query->nextResult()) {
            $name = (string)$result->get("category_name");
            $builder->setData($result->toArray());

            $link = new Container(false);
            $link->setTagName("a");
            $link->setComponentClass("");
            $link->setAttribute("href", $builder->toString());
            $link->setAttribute("title", $name);

            if ($result->get("pcpID")) {
                $si = new StorageItem((int)$result->get("pcpID"), "ProductCategoryPhotosBean");
                $img = new Component(false);
                $img->setTagName("img");
                $img->setComponentClass("");
                $img->setClosingTagRequired(false);
                $img->setAttribute("src", $si->hrefImage(ImageSize::Small->value)->toString());
                $img->setAttribute("alt", $name);
                $link->items()->append($img);
            }
            $text = new Component(false);
            $text->setTagName("span");
            $text->setComponentClass("");
            $text->setText($name);
            $link->items()->append($text);

            $item = new Container(false);
            $item->setComponentClass("item");
            $item->items()->append($link);
            $list->items()->append($item);
        }
        $query->free();

        if ($list->items()->count() > 0) {
            $list->render();
        }
    }

    /**
     * The caption of the list (listHeading(), else the final title of the page) and its description
     * @return void
     */
    protected function renderListHeader() : void
    {
        $page = SparkPage::Instance();

        $heading = $this->listHeading();
        if ($heading === "") $heading = $page ? $page->getTitle() : (string)$this->config->title;

        $cmp = new Component();
        $cmp->setTagName("h1");
        $cmp->setComponentClass("Caption");
        $cmp->setClassName("category_name");
        $cmp->setText($heading);
        $cmp->render();

        $description = $page ? $page->getMetaDescription() : "";
        if ($description) {
            $cmp = new Component(false);
            $cmp->setTagName("h2");
            $cmp->setComponentClass("Caption");
            $cmp->setClassName("category_description seo_description");
            $cmp->setText($description);
            $cmp->render();
        }
    }

    /**
     * The description of the category, else the 'product_list_footer' of the store config
     * @return void
     * @throws Exception
     */
    protected function renderListFooter() : void
    {
        $catID = $this->treeView->getSelectedID();

        $category_description = "";
        if ($catID > 0 && $this->product_categories->haveColumn("category_description")) {
            $category_description = $this->product_categories->getValue($catID, "category_description");
        }

        if (!$category_description) {
            $config = ConfigBean::Factory();
            $config->setSection("store_config");
            $category_description = $config->get("product_list_footer");
        }

        if ($category_description) {
            //the HTML of the category editor / the store config
            $cmp = new Component(false);
            $cmp->setTagName("section");
            $cmp->setComponentClass("category_description");
            $cmp->setContents($category_description);
            $cmp->render();
        }
    }

    /**
     * The prev / next links and the page number of the list; the list and the collection page data
     * @param SparkPage $page
     * @return void
     */
    public function finalizeHead(SparkPage $page) : void
    {
        AbstractResultView::AppendHeadLinks($this->view, $page);

        $currentURL = $page->currentURL()->fullURL();
        $title = $page->head()->getTitle();
        $description = $page->head()->getMeta("description");

        $this->view->setSchemaDescription($title);
        $this->view->setAttribute("itemid", $currentURL);
        $this->view->setSchemaURL($currentURL);

        $pageData = new LinkedData("CollectionPage");
        $pageData->set("name", $title);
        $pageData->set("description", $description);
        $pageData->set("url", $currentURL);

        $mainEntity = new LinkedData();
        $mainEntity->setID($currentURL);
        $pageData->set("mainEntity", $mainEntity->toArray());

        $pageScript = new LDJsonScript();
        $pageScript->setLinkedData($pageData);
        $page->head()->addScript($pageScript);
    }
}
