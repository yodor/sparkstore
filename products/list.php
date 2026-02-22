<?php
include_once("class/pages/ProductListPage.php");

$page = new ProductListPage();
$page->initialize();
$page->processInput();
$page->render();