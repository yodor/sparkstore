<?php
//
// products module - the catalog of the store sites. Every path is a content in the site StorePage frame; a site
// names its subclasses of the contents in its own path files (class/templates/modules/products/path).
// Site init.php runs after it and may add or change paths.
//
//  /products/                           the list (search, filters)        ProductListContent  (path/home.php)
//  /products/category/<catID>/<slug>/   a category                        ProductListContent  (path/category.php)
//  /products/promos/[<catID>/<slug>/]   promotions                        ProductListContent  (path/promos.php)
//  /products/search/[<catID>/<slug>/]   keyword search (noindex)          ProductListContent  (path/search.php)
//  /products/<prodID>/<slug>/           a product                         ProductDetailsContent (path/details.php)
//
include_once("store/pages/StorePage.php");

$module = Module::Active();
$module->pageClass = StorePage::class;

//the old script of a product (details.php?prodID=) - answered as the product path: one 301 to the product address
$module->addPathAlias("details.php", "details");

//old addresses of the list
$module->addRedirect("list.php", "");
$module->addRedirect("promo.php", "promos");
