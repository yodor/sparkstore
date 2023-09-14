<?php
include_once("beans/DBTableBean.php");

class ProductClassAttributesBean extends DBTableBean
{

    protected $createString = "CREATE TABLE `product_class_attributes` (
  `pcaID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pclsID` int(11) unsigned NOT NULL,
  `attrID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`pcaID`),
  UNIQUE KEY `class_attribute` (`pclsID`,`attrID`) USING BTREE,
  KEY `pclsID` (`pclsID`),
  KEY `attrID` (`attrID`),
  CONSTRAINT `product_class_attributes_ibfk_1` FOREIGN KEY (`attrID`) REFERENCES `attributes` (`attrID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_class_attributes_ibfk_2` FOREIGN KEY (`pclsID`) REFERENCES `product_classes` (`pclsID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    public function __construct()
    {
        parent::__construct("product_class_attributes");
    }

}

?>