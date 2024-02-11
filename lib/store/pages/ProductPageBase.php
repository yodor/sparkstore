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

    protected function prepareKeywords()
    {

    }

    protected function getCategoryKeywords(int $catID) : string
    {


        if (!$this->product_categories->haveColumn("category_keywords")) return "";

        $result = $this->product_categories->getParentNodes($catID, array("category_name",
            "category_keywords"));

        $keywords = array();
        foreach ($result as $item => $values) {
            if (!isset($values["category_keywords"]))continue;

            $category_keywords = trim($values["category_keywords"]);
            if (mb_strlen($category_keywords) < 1) continue;

            $keywords[] = $category_keywords;
        }

        return implode(", ",$keywords);
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
    protected function loadCategoryPath(int $nodeID)
    {
        $this->category_path = $this->product_categories->getParentNodes($nodeID, array("catID", "category_name"));
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

    protected function constructTitleArray() : array
    {
        $title = array();

        if (is_array($this->category_path) && count($this->category_path)>0) {
            //$catinfo = end($this->category_path);
            foreach ($this->category_path as $idx => $catinfo) {
                $title[] = $catinfo["category_name"];
            }
        }
        else if ($this->section) {
            $title[] = $this->section;
        }

        return $title;
    }
    protected function constructTitle()
    {
        if ($this->keyword_search->isProcessed()) {
            $this->setTitle("Резултати от търсене: ".mysql_real_unescape_string($this->keyword_search->getForm()->getInput("keyword")->getValue()));
            return;
        }

        $title = $this->constructTitleArray();
        if (count($title)>0) {
            $this->setTitle(constructSiteTitle($title));
        }
        else {
            parent::constructTitle();
        }
    }
}

?>
