<?php
include_once("class/pages/ProductDetailsPage.php");

$page = new ProductDetailsPage();
$page->initialize();
$page->processInput();
$page->render();