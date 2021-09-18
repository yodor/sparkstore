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

    static public function ParseAttributes(array $attributes)
    {
        $attr_list = explode("|", $attributes);
        $attr_all = array();
        if (is_array($attr_list)) {
            foreach ($attr_list as $idx => $pair) {
                list($name, $value) = explode(":", $pair);
                if ($name && $value) {
                    $attr_all[] = array("name" => $name, "value" => $value);
                }
            }
        }
        return $attr_all;
    }
}
?>