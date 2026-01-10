<?php
include_once("store/utils/ProductAttributesSQL.php");
include_once("beans/DBViewBean.php");

class ProductAttributes extends DBViewBean
{
    protected static ?SQLSelect $ProductAttributes = null;

    protected static string $Grouping = "";

    static public function SetProductAttributesSelect(SQLSelect $select) : void
    {
        ProductAttributes::$ProductAttributes = $select;
    }

    static public function ProductAttributesSelect() : SQLSelect
    {
        return ProductAttributes::$ProductAttributes;
    }

    public function __construct(string $table_name="product_attributes")
    {
        if (is_null(ProductAttributes::$ProductAttributes)) {
            ProductAttributes::$ProductAttributes = new ProductAttributesSQL();
        }

        $this->createString = "CREATE VIEW IF NOT EXISTS $table_name AS (".ProductAttributes::$ProductAttributes->getSQL().")";

        parent::__construct($table_name);

        $this->select->fields()->reset();
        $this->select->fields()->set(...$this->columnNames());
        $this->prkey = "prodID";
    }

    static public function DefaultGrouping() : string
    {
        return ProductAttributes::$Grouping;
    }

    static public function SetDefaultGrouping(string $grouping) : void
    {
        ProductAttributes::$Grouping = $grouping;
    }


}
?>