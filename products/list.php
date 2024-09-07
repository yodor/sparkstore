<?php
include_once("session.php");
include_once("store/pages/ProductListPageBase.php");

$page = new ProductListPageBase();
$bean = new SellableProducts();
$page->setSellableProducts($bean);

$page->initialize();

$page->processInput();

$page->startRender();

$page->renderContents();

$page->finishRender();
?>
