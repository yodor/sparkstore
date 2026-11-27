<?php
include_once("beans/DBViewBean.php");
include_once("store/utils/ProductsSQL.php");
include_once("store/beans/ProductAttributes.php");
include_once("templates/Module.php");

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

        //the view carries the version of its select as a column name: a changed select (the store, the site class, a
        //catalog part switched on or off) recreates it - read from the DESCRIBE the bean does anyway, no extra query
        $select = clone SellableProducts::$Products;
        $version = SellableProducts::VERSION_PREFIX . substr(md5($select->getSQL()), 0, 12);
        $select->alias("1", $version);
        $sql = $select->getSQL();

        $this->createString = "CREATE VIEW IF NOT EXISTS $table_name AS ($sql)";

        parent::__construct($table_name);

        if (!isset($this->columns[$version])) {
            $this->replaceView($table_name, $sql);
        }

        //the columns without the version column
        $columns = array_filter($this->columnNames(), fn(string $name) => !str_starts_with($name, SellableProducts::VERSION_PREFIX));
        $this->select->columns()->reset();
        $this->select->columns(...$columns);
        $this->prkey = "prodID";
    }

    /**
     * The prefix of the version column of the view
     */
    const string VERSION_PREFIX = "view_version_";

    /**
     * Recreate the view with the current select - only inside a module request (the site init has set the site's
     * select), so a plain script without the site init does not replace the site's view with the store default
     * @param string $table_name
     * @param string $sql
     * @return void
     */
    protected function replaceView(string $table_name, string $sql) : void
    {
        if (Module::Active() === null) {
            Debug::ErrorLog("View '$table_name' is not of the current select - recreated at the next page request");
            return;
        }
        try {
            static::CreateTable("CREATE OR REPLACE VIEW $table_name AS ($sql)");
            DBTableBean::ForgetColumnTypes($table_name);
            $this->initColumnTypes();
            Debug::ErrorLog("View '$table_name' recreated with the current select");
        }
        catch (Exception $e) {
            //the old view keeps working
            Debug::ErrorLog("View '$table_name' not recreated: " . $e->getMessage());
        }
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
                //the names match in lower case, the values keep their own (names of people, publishers)
                $attributeName = mb_strtolower($name);
                $found = false;
                foreach ($supported as $itemProp=>$matches) {
                    if (in_array($attributeName, $matches)) {
                        $found = true;
                        $function_supported($itemProp, $value);
                        break;
                    }
                }
                if (!$found) {
                    $unsupported[$name] = $value;
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