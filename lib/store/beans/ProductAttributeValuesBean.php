<?php
include_once("beans/DBTableBean.php");

class ProductAttributeValuesBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `product_attribute_values` (
  `cavID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prodID` int(11) unsigned NOT NULL,
  `caID` int(11) unsigned NOT NULL,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cavID`),
  UNIQUE KEY `product_attributes` (`prodID`,`caID`),
  KEY `caID` (`caID`),
  KEY `prodID` (`prodID`),
  CONSTRAINT `product_attribute_values_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_attribute_values_ibfk_2` FOREIGN KEY (`caID`) REFERENCES `class_attributes` (`caID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("product_attribute_values");
    }

}

?>
