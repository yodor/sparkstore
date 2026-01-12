<?php
include_once("store/utils/ProductAttributesSQL.php");
include_once("beans/DBViewBean.php");

class ProductAttributes extends DBViewBean
{
    protected static ?SQLSelect $ProductAttributes = null;

    public function __construct(string $table_name="product_attributes")
    {
        ProductAttributes::$ProductAttributes = new ProductAttributesSQL();

        $this->createString = "CREATE VIEW IF NOT EXISTS $table_name AS (".ProductAttributes::$ProductAttributes->getSQL().")";

        parent::__construct($table_name);

        $this->select->fields()->reset();
        $this->select->fields()->set(...$this->columnNames());
        $this->prkey = "prodID";
    }

}
?>