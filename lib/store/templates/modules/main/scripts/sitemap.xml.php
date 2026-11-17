<?php
//sitemap.xml of the main module: the cache written by createsitemap.php (CACHE_PATH/sitemap.xml) when there is one,
//else the live sitemap (sparksitemap.php - menu, products, categories, $items_add of the site script). ?live=1: live.
include_once("storage/SparkFile.php");

header("Content-Type: application/xml; charset=UTF-8");

$cached = new SparkFile();
$cached->setPath(Spark::Get(Config::CACHE_PATH));
$cached->setFilename("sitemap.xml");

if (!isset($_GET["live"]) && $cached->exists()) {
    $cached->open("r");
    $cached->passthru();
    $cached->close();
    return;
}

include_once("sparksitemap.php");
