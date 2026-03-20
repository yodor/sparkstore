<?php
include_once("sql/SQLSelect.php");

class ProductAttributesSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        $this->set("pcav.prodID");
        $this->setAliasExpression("a.name", "attribute_name");
        $this->setAliasExpression("pcav.value", "attribute_value");

        $this->from("product_class_attribute_values pcav")
            ->innerJoin("product_class_attributes pca")->on("pca.pcaID = pcav.pcaID")
            ->innerJoin("attributes a")->on("a.attrID = pca.attrID");
    }

}