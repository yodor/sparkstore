<?php
include_once("session.php");
include_once("class/pages/ProductListPage.php");

CategoryURL::$urlCategorySlug = "/products/promos/";
$page = new ProductListPage();

$clause = new SQLClause();
$clause->setExpression("(discount_percent > 0 OR promo_price > 0)", "", "");
$page->getSellableProducts()->select()->where()->append($clause);

$page->initialize();
$page->processInput();
$page->render();