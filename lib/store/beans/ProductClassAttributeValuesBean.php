<?php
include_once("beans/DBTableBean.php");

class ProductClassAttributeValuesBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `product_class_attribute_values` (
  `pcavID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prodID` int(11) unsigned NOT NULL,
  `pcaID` int(11) unsigned NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pcavID`),
  UNIQUE KEY `product_value` (`prodID`,`pcaID`),
  KEY `prodID` (`prodID`),
  KEY `pcaID` (`pcaID`),
  CONSTRAINT `product_class_attribute_values_ibfk_1` FOREIGN KEY (`pcaID`) REFERENCES `product_class_attributes` (`pcaID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_class_attribute_values_ibfk_2` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    public function __construct()
    {
        parent::__construct("product_class_attribute_values");
    }

}

?>