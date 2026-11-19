<?php
include_once("templates/TemplateContent.php");
include_once("components/BreadcrumbList.php");
include_once("components/Action.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/utils/url/CategoryURL.php");
include_once("store/utils/url/ProductListURL.php");
include_once("utils/menu/IMenuAddresses.php");
include_once("utils/SEOData.php");

/**
 * Base of the products module contents (StorePage frame): the category branch of the page, the breadcrumb, the
 * main menu selection by category (IMenuAddresses) and the title / description from the categories (their seo
 * title and description, the deepest one that has them).
 */
abstract class ProductContent extends TemplateContent implements IMenuAddresses
{
    protected ?ProductCategoriesBean $product_categories = null;

    /**
     * The category branch of the page from the top category down (catID, category_name, seo title / description)
     * @var array
     */
    protected array $category_path = array();

    protected ?BreadcrumbList $breadcrumb = null;

    public function __construct()
    {
        parent::__construct();
        $this->product_categories = new ProductCategoriesBean();
        $this->breadcrumb = new BreadcrumbList();
    }

    public function getCategoryPath() : array
    {
        return $this->category_path;
    }

    /**
     * Load the category branch of '$nodeID' (from the top down)
     * @param int $nodeID
     * @return void
     * @throws Exception
     */
    protected function loadCategoryPath(int $nodeID) : void
    {
        $columns = array("catID", "category_name", "seo_title", "seo_description");
        $this->category_path = $this->product_categories->getParentNodes($nodeID, $columns);
    }

    /**
     * The categories of the page from the deepest to the top (IMenuAddresses)
     * @return array
     */
    public function menuAddresses() : array
    {
        $addresses = array();
        foreach (array_reverse($this->category_path) as $category) {
            $url = new CategoryURL();
            $url->setData($category);
            $addresses[] = $url;
        }
        return $addresses;
    }

    /**
     * The breadcrumb actions: the products list (the home action of the store frame), the categories
     * @return array<Action>
     */
    protected function constructPathActions() : array
    {
        $actions = array();
        $page = SparkPage::Instance();
        if ($page instanceof StorePage && $page->getHomeAction() instanceof Action) {
            $actions[] = $page->getHomeAction();
        }

        $link = new ProductListURL();
        foreach ($this->category_path as $category) {
            $link = new CategoryURL($link);
            $link->setData($category);
            $actions[] = new Action($category["category_name"], $link->toString(), array());
        }
        return $actions;
    }

    protected function fillBreadCrumb() : void
    {
        $this->breadcrumb->items()->clear();
        foreach ($this->constructPathActions() as $action) {
            $this->breadcrumb->items()->append($action);
        }
    }

    /**
     * Title and description of the category branch: the SEO title or the name of the deepest category, the SEO
     * description (or the description) of the deepest category that has one - [title, description]
     * @return array
     */
    protected function categoryTitleDescription() : array
    {
        $title = "";
        $description = "";
        foreach ($this->category_path as $element) {
            $title = SEOData::Title($element, (string)$element["category_name"]);
            $description = SEOData::Description($element, (string)($element["category_description"] ?? "")) ?: $description;
        }
        return array($title, $description);
    }

    /**
     * The name of the deepest category of the branch - "" without a category
     * @return string
     */
    protected function categoryName() : string
    {
        $count = count($this->category_path);
        return ($count > 0) ? (string)$this->category_path[$count - 1]["category_name"] : "";
    }

    public function component() : Component
    {
        return $this->cmp;
    }

    protected function getContentTitle() : string
    {
        return "";
    }
}
