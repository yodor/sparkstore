<?php
include_once("beans/DBViewBean.php");
include_once("store/utils/ProductsSQL.php");
include_once("store/beans/ProductAttributes.php");

class SellableProducts extends DBViewBean
{
    protected static ?SQLSelect $Products = null;

    //specify grouping as it is used in aggregate select with the categories
    protected static string $Grouping = " prodID ";

    static public function SetProductsSelect(SQLSelect $select) : void
    {
        SellableProducts::$Products = $select;
    }

    static public function ProductsSelect() : SQLSelect
    {
        return SellableProducts::$Products;
    }

    public function __construct(string $table_name="sellable_products")
    {
        if (is_null(SellableProducts::$Products)) {
            SellableProducts::$Products = new ProductsSQL();
        }

        try {
            @new ProductAttributes();
        }
        catch (Exception $e) {

        }

        $this->createString = "CREATE VIEW IF NOT EXISTS $table_name AS (".SellableProducts::$Products->getSQL().")";

        parent::__construct($table_name);

        $this->select->fields()->reset();
        $this->select->fields()->set(...$this->columnNames());
        $this->prkey = "prodID";
    }

    static public function DefaultGrouping()
    {
        return SellableProducts::$Grouping;
    }

    static public function SetDefaultGrouping(string $grouping)
    {
        SellableProducts::$Grouping = $grouping;
    }

    static public function ParseAttributes(?string $attributes)
    {
        $attr_all = array();
        if (is_null($attributes)) return $attr_all;

        $attr_list = explode("|", $attributes);

        if (is_array($attr_list)) {
            foreach ($attr_list as $idx => $pair) {
                if ($pair) {
                    list($name, $value) = explode(":", $pair);
                    if ($name && $value) {
                        $attr_all[$name] = $value;
                    }
                }
            }
        }
        return $attr_all;
    }

    public static function AttributesWalker(array $attributes, array $supported, Closure $function_supported, ?Closure $function_unsupported=null) : void
    {
        $unsupported = array();
        foreach ($attributes as $name => $value) {

            if ($name && $value) {
                $attributeName = mb_strtolower($name);
                $attributeValue = mb_strtolower($value);
                $found = false;
                foreach ($supported as $itemProp=>$matches) {
                    if (in_array($attributeName, $matches)) {
                        $found = true;
                        $function_supported($itemProp, $attributeValue);
                        break;
                    }
                }
                if (!$found) {
                    $unsupported[$attributeName] = $attributeValue;
                }
            }
        }
        if (count($unsupported)>0 && ($function_unsupported instanceof Closure)) {
            $function_unsupported($unsupported);
        }
    }

    public static function AttributesMeta(array $attributes, array $supported) : void
    {

        $meta = function($itemProp, $attributeValue) {
            echo "<meta itemprop='$itemProp' content='".Spark::AttributeValue($attributeValue)."'>";
        };
        SellableProducts::AttributesWalker($attributes, $supported, $meta);

    }
    public static function HasAttribute(array $attributes, string $name) : bool
    {
        return isset($attributes[$name]) && strlen((string)$attributes[$name])>0;
    }
}
?>