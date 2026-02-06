<?php
include_once("utils/url/URL.php");

class ProductListURL extends URL
{
    public static string $urlProductList = "/products/list.php";

    /**
     * Construct URL pointing to the default products list page
     * LOCAL./products/list.php
     */
    public function __construct()
    {
        parent::__construct();
        $this->fromString(Spark::Get(Config::LOCAL).self::$urlProductList);
    }
}
?>