<?php
//
// news module - store part, included after the core init.php: the storefront look - the site StorePage frame,
// the body class and the stylesheet of the module
//

include_once("store/pages/StorePage.php");

$module = Module::Active();
$module->pageClass = StorePage::class;
$module->addPageClass("NewsPage");
$module->addPageCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/news.css");
