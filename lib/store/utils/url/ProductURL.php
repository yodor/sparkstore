<?php
include_once("utils/url/URL.php");

class ProductURL extends URL
{
    public static string $urlProductSlug = "/products/";
    public static string $urlProduct = "/products/details.php";

    public function __construct(?URL $other=null)
    {
        parent::__construct();
        if (Spark::GetBoolean(StoreConfig::PRODUCT_ITEM_SLUG)) {
            $this->fromString(Spark::Get(Config::LOCAL).self::$urlProductSlug);
            $this->add(new PathParameter("prodID", "prodID", false));
            $this->add(new PathParameter("product_name", "product_name", true));
        }
        else {
            $this->fromString(Spark::Get(Config::LOCAL).self::$urlProduct);
            $this->add(new DataParameter("prodID"));
        }

        if (!is_null($other)) {
            $this->copyParametersFrom($other, false);
        }

    }

    public function setProductID(int $prodID) : void
    {
        $this->setData(array("prodID"=>$prodID));
    }

    public function setProductName(string $productName) : void
    {
        $this->setData(array("product_name"=>$productName));
    }

}