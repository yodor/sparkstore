<?php
//
// products module - the catalog of the store sites. The product pages do their own work (no template content):
// the module routes each path to its page class - the site subclasses (class/pages). Site init.php runs after it
// and may add or change paths.
//
//  /products/                           the list (search, filters)        ProductListPage
//  /products/category/<catID>/<slug>/   a category                        ProductListPage
//  /products/promos/[<catID>/<slug>/]   promotions                        ProductPromoListPage
//  /products/<prodID>/<slug>/           a product                         ProductDetailsPage
//
$module = Module::Active();
if (!$module->pageClass) {
    include_once("class/pages/ProductListPage.php");
    $module->pageClass = "ProductListPage";
}
$module->addPagePath("category", "ProductListPage", "class/pages/ProductListPage.php");
$module->addPagePath("details", "ProductDetailsPage", "class/pages/ProductDetailsPage.php");
$module->addPagePath("promos", "ProductPromoListPage", "store/pages/ProductPromoListPage.php");
//the old script of a product - its page answers with 301 to the product address
$module->addPagePath("details.php", "ProductDetailsPage", "class/pages/ProductDetailsPage.php");

//old addresses of the list
$module->addRedirect("list.php", "");
$module->addRedirect("promo.php", "promos");
