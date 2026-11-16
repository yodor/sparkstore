<?php
include_once("utils/url/URL.php");

class ProductURL extends URL
{
    /**
     * Product pages: <LOCAL>/products/<prodID>/<product name slug>/ (modules/products/.htaccess -> path details, prodID)
     */
    public static string $urlProductSlug = "/products/";

    public function __construct(?URL $other=null)
    {
        parent::__construct();

        $this->fromString(Spark::Get(Config::LOCAL).self::$urlProductSlug);
        $this->add(new PathParameter("prodID", "prodID", false));
        $this->add(new PathParameter("product_name", "product_name", true));

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