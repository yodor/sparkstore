<?php
include_once("class/pages/StorePage.php");
include_once("components/BreadcrumbList.php");
include_once("store/components/renderers/items/ProductListItem.php");
include_once("store/beans/SellableProducts.php");
include_once("store/utils/url/CategoryURL.php");
include_once("store/utils/url/ProductURL.php");
include_once("store/beans/ProductViewLogBean.php");

class ProductPageBase extends StorePage
{
    protected string $products_title = "Продукти";

    /**
     * @var SellableProducts|null
     */
    protected $bean = null;

    /**
     * @var SectionsBean|null
     */
    public ?SectionsBean $sections = NULL;

    /**
     * @var ProductCategoriesBean
     */
    public ?ProductCategoriesBean $product_categories = NULL;

    /**
     * Currently selected section (processed as get variable)
     * @var string
     */
    protected string $section = "";

    /**
     * Array holding the current selected category branch starting from nodeID to the top
     * @var array
     */
    protected array $category_path = array();

    protected ?BreadcrumbList $breadcrumb = null;

    /**
     * @var ProductListFilter|null
     */
    protected ?ProductListFilter $filters = NULL;

    /**
     * Other get variable filters
     * @var SparkMap|null
     */
    protected ?SparkMap $property_filter = NULL;

    public function __construct()
    {
        parent::__construct();

        new ProductViewLogBean();

        $this->product_categories = new ProductCategoriesBean();
        $this->sections = new SectionsBean();
        $this->breadcrumb = new BreadcrumbList();

    }

    public function initialize() : void
    {

    }

    public function processInput() : void
    {

    }

    public function setSellableProducts(DBTableBean $bean) : void
    {
        $this->bean = $bean;
    }

    public function getSellableProducts() : SellableProducts
    {
        return $this->bean;
    }

    public function getCategoryPath() : array
    {
        return $this->category_path;
    }

    /**
     * Load the current selected category branch into the category_path array.
     * Starting from nodeID to the top
     */
    protected function loadCategoryPath(int $nodeID): void
    {
        $columns = array("catID", "category_name");
        if ($this->product_categories->haveColumn("category_seotitle")) {
            $columns[] = "category_seotitle";
        }
        if ($this->product_categories->haveColumn("category_seodescription")) {
            $columns[] = "category_seodescription";
        }
        $this->category_path = $this->product_categories->getParentNodes($nodeID, $columns);
    }

    protected function fillBreadCrumb() : void
    {
        $actions = $this->constructPathActions();

        $this->breadcrumb->items()->clear();
        foreach ($actions as $idx=>$cmp) {
            $this->breadcrumb->items()->append($cmp);
        }

    }

    protected function constructPathActions() : array
    {
        $actions = array();

        $link = new ProductListURL();

        if ($this->keyword_search->isProcessed()) {
            $current = URL::Current();
            foreach ($this->keyword_search->getForm()->inputNames() as $idx => $name) {
                if ($current->contains($name)) {
                    $link->add($current->get($name));
                }

            }
            if ($current->contains(KeywordSearch::SUBMIT_KEY)) {
                $link->add($current->get(KeywordSearch::SUBMIT_KEY));
            }

            $search_title = tr("Search results").": ".mysql_real_unescape_string($this->keyword_search->getForm()->getInput("keyword")->getValue());
            $search_action = new Action($search_title,  $link, array());
            $actions[] = $search_action;
        }
        else if ($this->filters instanceof ProductListFilter && count($this->filters->getActiveFilters())>0) {
            $link = URL::Current();
            $link->remove("catID");
            $search_action = new Action(tr("Search results"), $link->toString(), array());
            $actions[] = $search_action;
        }
        else {
            $product_action = new Action("Начало", LOCAL . "/home.php", array());
            $actions[] = $product_action;
        }

        if ($this->property_filter instanceof SparkMap) {
            $iterator = $this->property_filter->iterator();
            while ($filter = $iterator->next()) {
                if ($filter instanceof GETProcessor && $filter->isProcessed()) {
                    $result = $filter->getTitle();

                    $link->add(new DataParameter($filter->getName(), urlencode($filter->getValue())));
                    $property_action = new Action($result, $link->toString(), array());
                    $actions[] = $property_action;
                }
            }
        }

        foreach ($this->category_path as $idx => $category) {
            $link = new CategoryURL($link);
            $link->setData($category);

            $category_action = new Action($category["category_name"], $link->toString(), array());
            $actions[] = $category_action;
        }

        return $actions;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function applyTitleDescription() : void
    {

        $title = "";
        $description = "";

        //use the title or seotitle of the selected category
        //go from top to bottom so to use description of parent category if child is empty
        foreach ($this->category_path as $idx => $element) {
            if (isset($element["category_seotitle"]) && mb_strlen($element["category_seotitle"]) > 0) {
                $title = $element["category_seotitle"];
            } else {
                $title = $element["category_name"];
            }
            if (isset($element["category_seodescription"]) && mb_strlen($element["category_seodescription"])>0) {
                $description = $element["category_seodescription"];
            }
            else if (isset($element["category_description"]) && mb_strlen($element["category_description"])>0) {
                $description = $element["category_description"];
            }
        }

        //prefer section seo description if present - fetch additional section data
        if ($this->section) {
            $columns = array();
            if ($this->sections->haveColumn("section_seodescription")) {
                $columns[] = "section_seodescription";
            }
            $result = $this->sections->getResult("section_title", $this->section, ...$columns);

            if (isset($result["section_seodescription"]) && $result["section_seodescription"]) {
                $description = $result["section_seodescription"];
            }

            $title = $this->section.($title?" - ".$title:"");
        }

        if ($this->keyword_search->isProcessed()) {
            $search_value = $this->keyword_search->getForm()->getInput("keyword")->getValue();
            $title = tr("Search results")." - ".mysql_real_unescape_string($search_value).($title?" - ".$title:"");
        }
        else if ($this->filters instanceof ProductListFilter && count($this->filters->getActiveFilters())>0) {
            $title = tr("Search results").($title?" - ".$title:"");
        }

        if (mb_strlen($title)>0) {
            $this->preferred_title = $title;
        }

        if (mb_strlen($description)>0) {
            $this->description = $description;
        }

        parent::applyTitleDescription();
    }
}

?>
