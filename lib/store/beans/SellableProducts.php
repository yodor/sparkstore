<?php
include_once("class/utils/ProductsSQL.php");
include_once("beans/DBViewBean.php");

class SellableProducts extends DBViewBean
{
    protected $products = null;

    public function __construct()
    {
        $this->products  = new ProductsSQL();
        $this->createString = "CREATE VIEW IF NOT EXISTS sellable_products AS ({$this->products->getSQL()})";
        parent::__construct("sellable_products");

        $this->select->fields()->set(...$this->columnNames());
        $this->prkey = "piID";
    }

    static public function DefaultGrouping()
    {
        return " prodID, color ";
    }
}
?>