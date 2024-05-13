<?php
include_once("class/pages/StorePage.php");
include_once("components/BreadcrumbList.php");
include_once("store/components/renderers/items/ProductListItem.php");
include_once("store/beans/SellableProducts.php");

class ProductPageBase extends StorePage
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
     * Currently selected section (processed as get variable)
     * @var string
     */
    protected $section = "";

    /**
     * Array holding the current selected category branch starting from nodeID to the top
     * @var array
     */
    protected $category_path = array();


    protected $breadcrumb = null;


    public function __construct()
    {
        parent::__construct();

        $this->product_categories = new ProductCategoriesBean();
        $this->sections = new SectionsBean();
        $this->breadcrumb = new BreadcrumbList();

    }

    /**
     * Construct the page keywords meta tag contents
     * Use the summary of current category and its parents keywords
     * @return string Return the keywords set by this method
     */
    protected function prepareKeywords() : string
    {
        $keywords = "";

        if (count($this->category_path)>0) {
            $keywords_all = array();
            foreach ($this->category_path as $idx=>$element) {
                if (isset($element["category_keywords"])) {
                    $category_keywords = sanitizeKeywords($element["category_keywords"]);
                    if (mb_strlen($category_keywords) > 0) {
                        $keywords_all[] = $category_keywords;
                    }
                }

            }
            $keywords = implode(", ", $keywords_all);
        }

        if (mb_strlen($keywords)>0) {
            $this->keywords = $keywords;
        }

        return $keywords;
    }

    /**
     * Sets the value of 'description' page property, used to overload the description meta-tag.
     * Uses the first available 'category_seodescription' of the selected category.
     * If category_seodescription is empty does not override the default page meta-tag
     * @return string
     */
    protected function prepareDescription() : string
    {
        $description = "";
        if (count($this->category_path)>0) {
            $category_path = array_reverse($this->category_path, true);

            foreach ($category_path as $idx=>$element) {
                if (isset($element["category_seodescription"]) && mb_strlen($element["category_seodescription"])>0) {
                    $description = $element["category_seodescription"];
                }
                if (mb_strlen($description)>0) break;
            }
        }
        if (mb_strlen($description)>0) {
            $this->description = $description;
        }
        return $description;
    }

    public function setSellableProducts(DBTableBean $bean)
    {
        $this->bean = $bean;
    }

    public function getCategoryPath()
    {
        return $this->category_path;
    }

    /**
     * Load the current selected category branch into the category_path array. Starting from nodeID to the top
     * @param int $nodeID
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
        if ($this->product_categories->haveColumn("category_keywords")) {
            $columns[] = "category_keywords";
        }
        $this->category_path = $this->product_categories->getParentNodes($nodeID, $columns);
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

        if ($this->keyword_search->isProcessed()) {
            $atitle = "Резултати от търсене: ".mysql_real_unescape_string($this->keyword_search->getForm()->getInput("keyword")->getValue());
            $actions[] = new Action($atitle, $this->getPageURL(), array());
        }
        else {
            $actions[] = new Action(tr($this->products_title), $link->url(), array());
        }

        if ($this->section) {
            $link->add(new URLParameter("section", $this->section));
            $actions[] = new Action($this->section, $link->url(), array());
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
     * Construct the page title tag. Called after finish render of the page class
     * Preference of title tag
     * - search results title (local searching)
     * - category_title/category_seotitle
     * - section name
     * - default implementation of StorePageBase
     * @return void
     * @throws Exception
     */
    protected function constructTitle() : void
    {
        $title = "";
        if ($this->keyword_search->isProcessed()) {
            $search_value = $this->keyword_search->getForm()->getInput("keyword")->getValue();
            $title = "Резултати от търсене: ".mysql_real_unescape_string($search_value);
        }

        else if (is_array($this->category_path) && count($this->category_path)>0) {
            //use the title or seotitle of the selected category
//            foreach ($this->category_path as $idx => $element) {
//                $title[] = $element["category_name"];
//            }
            $element = end($this->category_path);
            if (isset($element["category_seotitle"]) && mb_strlen($element["category_seotitle"])>0) {
                $title = $element["category_seotitle"];
            }
            else {
                $title = $element["category_name"];
            }
        }
        else if ($this->section) {
            $title = $this->section;
        }

        if (mb_strlen($title)>0) {
            $this->setTitle($title);
        }
        else {
            parent::constructTitle();
        }
    }
}

?>
