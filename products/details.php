<?php
include_once("session.php");
include_once("class/pages/ProductDetailsPage.php");

$page = new ProductDetailsPageBase();
$page->initialize();

$page->startRender();

$page->renderContents();

$page->finishRender();
?>