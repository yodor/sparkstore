<?php
include_once("session.php");
include_once("class/pages/ProductListPage.php");

$page = new ProductListPage();
$page->category_slug_name = "/products/promos/";

$clause = new SQLClause();
$clause->setExpression("(discount_percent > 0 OR promo_price > 0)", "", "");
$page->getSellableProducts()->select()->where()->append($clause);

$page->initialize();
$page->processInput();
$page->render();
?>
