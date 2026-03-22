<?php
include_once("sql/SQLSelect.php");

class ProductAttributesSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        $this->columns("pcav.prodID");
        $this->alias("a.name", "attribute_name");
        $this->alias("pcav.value", "attribute_value");

        $this->from("product_class_attribute_values pcav")
            ->innerJoin("product_class_attributes pca")->on("pca.pcaID = pcav.pcaID")
            ->innerJoin("attributes a")->on("a.attrID = pca.attrID");
    }

}