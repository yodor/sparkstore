<?php
include_once("beans/DBTableBean.php");

class ProductClassAttributeValuesBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `product_class_attribute_values` (
  `pcavID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prodID` int(11) unsigned NOT NULL,
  `pcaID` int(11) unsigned NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`pcavID`) USING BTREE,
  UNIQUE KEY `product_attributes` (`prodID`,`pcaID`),
  KEY `pcaID` (`pcaID`),
  KEY `prodID` (`prodID`),
  KEY `pcaID_2` (`pcaID`,`value`),
  KEY `prodID_2` (`prodID`,`pcaID`),
  FULLTEXT KEY `value` (`value`),
  CONSTRAINT `product_class_attribute_values_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_class_attribute_values_ibfk_2` FOREIGN KEY (`pcaID`) REFERENCES `product_class_attributes` (`pcaID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci";

    public function __construct()
    {
        parent::__construct("product_class_attribute_values");
    }

}