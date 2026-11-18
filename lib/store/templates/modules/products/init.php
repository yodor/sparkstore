<?php
//
// products module - the catalog of the store sites. A product is a content in the site StorePage frame
// (path/details.php: ProductDetailsContent - a site names its subclass in its own path file). The lists still do
// their own work: the module routes their paths to the page classes (class/pages, the site subclasses).
// Site init.php runs after it and may add or change paths.
//
//  /products/                           the list (search, filters)        ProductListPage
//  /products/category/<catID>/<slug>/   a category                        ProductListPage
//  /products/promos/[<catID>/<slug>/]   promotions                        ProductPromoListPage
//  /products/<prodID>/<slug>/           a product                         ProductDetailsContent (StorePage)
//  /products/search/[<catID>/<slug>/]   keyword search (noindex)          ProductSearchListPage
//
$module = Module::Active();
if (!$module->pageClass) {
    include_once("class/pages/ProductListPage.php");
    $module->pageClass = "ProductListPage";
}
$module->addPagePath("category", "ProductListPage", "class/pages/ProductListPage.php");
//a product: the content of path/details.php in the site StorePage frame
$module->addPagePath("details", "StorePage", "class/pages/StorePage.php");
$module->addPagePath("search", "ProductSearchListPage", "store/pages/ProductSearchListPage.php");
$module->addPagePath("promos", "ProductPromoListPage", "store/pages/ProductPromoListPage.php");
//the old script of a product (details.php?prodID=) - answered as the product path: one 301 to the product address
$module->addPathAlias("details.php", "details");

//old addresses of the list
$module->addRedirect("list.php", "");
$module->addRedirect("promo.php", "promos");
