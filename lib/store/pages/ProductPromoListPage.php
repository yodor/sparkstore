<?php
include_once("class/pages/ProductListPage.php");
include_once("store/utils/url/CategoryURL.php");

/**
 * The promotions list (products module path 'promos'): the site list page with products on promotion only;
 * category addresses /products/promos/<catID>/<slug>/
 */
class ProductPromoListPage extends ProductListPage
{
    public function __construct()
    {
        CategoryURL::$urlCategorySlug = "/products/promos/";
        parent::__construct();
        $this->listAddress = Spark::Get(Config::LOCAL) . "/products/promos/";

        $clause = new SQLClause();
        $clause->setExpression("(discount_percent > 0 OR promo_price > 0)");
        $this->getSellableProducts()->select()->where()->append($clause);
    }
}
