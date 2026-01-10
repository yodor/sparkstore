<?php
include_once("sql/SQLSelect.php");

class ProductAttributesSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        $this->fields()->set(            "pcav.prodID");
        $this->fields()->setExpression("a.name", "attribute_name");
        $this->fields()->setExpression("pcav.value", "attribute_value");

        $this->from = " product_class_attribute_values pcav 
                INNER JOIN product_class_attributes pca ON pca.pcaID = pcav.pcaID 
                INNER JOIN attributes a ON a.attrID = pca.attrID";
    }

}
?>