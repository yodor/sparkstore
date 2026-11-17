<?php
include_once("utils/url/URL.php");

/**
 * The product search of the products module: LOCAL/products/search/ (the keyword form goes here) - categories of the
 * results at /products/search/<catID>/<slug>/. Search results are not indexed (noindex, robots.txt).
 */
class ProductSearchURL extends URL
{
    public static string $urlProductSearch = "/products/search/";

    public function __construct()
    {
        parent::__construct();
        $this->fromString(Spark::Get(Config::LOCAL) . self::$urlProductSearch);
    }
}
