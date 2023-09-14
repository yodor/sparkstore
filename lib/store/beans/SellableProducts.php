<?php
include_once("class/utils/ProductsSQL.php");
include_once("beans/DBViewBean.php");

class SellableProducts extends DBViewBean
{
    protected $products = null;

    //specify grouping as it is used in aggregate select with the categories
    protected static $default_grouping = " prodID ";

    public function __construct()
    {
        $this->products  = new ProductsSQL();
        //echo $this->products->getSQL();

        $this->createString = "CREATE VIEW IF NOT EXISTS sellable_products AS ({$this->products->getSQL()})";
        parent::__construct("sellable_products");

        $this->select->fields()->set(...$this->columnNames());
        $this->prkey = "prodID";
    }

    static public function DefaultGrouping()
    {
        return self::$default_grouping;
    }

    static public function SetDefaultGrouping(string $grouping)
    {
        self::$default_grouping = $grouping;
    }

    static public function ParseAttributes(?string $attributes)
    {
        $attr_all = array();
        if (is_null($attributes)) return $attr_all;

        $attr_list = explode("|", $attributes);

        if (is_array($attr_list)) {
            foreach ($attr_list as $idx => $pair) {
                list($name, $value) = explode(":", $pair);
                if ($name && $value) {
                    $attr_all[$name] = $value;
                }
            }
        }
        return $attr_all;
    }
}
?>