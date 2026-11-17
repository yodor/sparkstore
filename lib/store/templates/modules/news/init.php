<?php
//
// news module - store part, included after the core init.php: the storefront look - the site StorePage frame,
// the body class and the stylesheet of the module
//
include_once("class/pages/StorePage.php");

$module = Module::Active();
$module->pageClass = "StorePage";
$module->addPageClass("NewsPage");
$module->addPageCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/news.css");
