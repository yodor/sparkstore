<?php
include_once("class/pages/StorePage.php");
include_once("components/BreadcrumbList.php");
include_once("store/components/renderers/items/ProductListItem.php");
include_once("store/beans/SellableProducts.php");
include_once("store/utils/url/CategoryURL.php");
include_once("store/utils/url/ProductURL.php");

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

    public function __construct()
    {
        parent::__construct();

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

    public function renderCategoryPath() : void
    {
        $actions = $this->constructPathActions();

        $this->breadcrumb->items()->clear();
        foreach ($actions as $idx=>$cmp) {
            $this->breadcrumb->items()->append($cmp);
        }

        $this->breadcrumb->render();
    }

    protected function constructPathActions() : array
    {
        $actions = array();

        $link = new URL(LOCAL."/products/list.php");
        $current = URL::Current();


        if ($this->keyword_search->isProcessed()) {
            foreach ($this->keyword_search->getForm()->inputNames() as $idx => $name) {
                $link->add($current->get($name));
            }
            $link->add($current->get(KeywordSearch::SUBMIT_KEY));

            $search_title = tr("Резултати от търсене").": ".mysql_real_unescape_string($this->keyword_search->getForm()->getInput("keyword")->getValue());
            $search_action = new Action($search_title,  $link, array());
            $search_action->translation_enabled = false;
            $actions[] = $search_action;
        }
        else {
            $product_action = new Action(tr($this->products_title), $link->toString() , array());
            $product_action->translation_enabled = false;
            $actions[] = $product_action;
        }

        if ($this->section) {
            $link->add(new URLParameter("section", $this->section));
            $section_action = new Action(tr($this->section), $link->toString(), array());
            $section_action->translation_enabled = false;
            $actions[] = $section_action;
        }

        foreach ($this->category_path as $idx => $category) {
            $catLink = new CategoryURL($link);
            $catLink->setData($category);

            $category_action = new Action($category["category_name"], $catLink->toString(), array());
            $category_action->translation_enabled = false;
            $actions[] = $category_action;

        }

        return $actions;
    }

    protected function headFinalize() : void
    {

        $title = "";
        $description = "";

        //use the title or seotitle of the selected category
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

        if ($this->keyword_search->isProcessed()) {
            $search_value = $this->keyword_search->getForm()->getInput("keyword")->getValue();
            $title = tr("Резултати от търсене").": ".mysql_real_unescape_string($search_value);
        }

        if (mb_strlen($title)>0) {
            $this->preferred_title = $title;
        }

        if (mb_strlen($description)>0) {
            $this->description = $description;
        }

        parent::headFinalize();
    }
}

?>
