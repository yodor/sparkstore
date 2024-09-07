<?php
include_once("session.php");
include_once("store/pages/ProductListPageBase.php");

$page = new ProductListPageBase();

$bean = new SellableProducts();

$clause = new SQLClause();
$clause->setExpression("(discount_percent > 0 OR promo_price > 0)", "", "");
$bean->select()->where()->append($clause);
$page->setSellableProducts($bean);

$page->initialize();

$page->processInput();

$page->startRender();

$page->renderContents();

$page->finishRender();
?>
