<?php
include_once("utils/url/URL.php");

class CategoryURL extends URL
{
    public static string $urlCategorySlug = "/products/category/";

    public function __construct(?URL $other=null)
    {
        parent::__construct();
        if (CATEGORY_ITEM_SLUG) {
            $this->fromString(LOCAL.self::$urlCategorySlug);
            $this->add(new PathParameter("catID", "catID", false));
            $this->add(new PathParameter("category_name", "category_name", true));
        }
        else {
            $this->add(new DataParameter("catID"));
        }

        if (!is_null($other)) {
            $this->copyParametersFrom($other, false);
        }
    }
    public function setCategoryID(int $catID) : void
    {
        $this->setData(array("catID"=>$catID));
    }
    public function setCategoryName(string $categoryName) : void
    {
        $this->setData(array("category_name"=>$categoryName));
    }

}
?>