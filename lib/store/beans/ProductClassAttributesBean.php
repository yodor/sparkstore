<?php
include_once("beans/DBTableBean.php");

class ProductClassAttributesBean extends DBTableBean
{

    protected string $createString = "CREATE TABLE `product_class_attributes` (
  `pcaID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pclsID` int(11) unsigned NOT NULL,
  `attrID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`pcaID`),
  UNIQUE KEY `attrID_2` (`attrID`,`pclsID`),
  KEY `pclsID` (`pclsID`),
  KEY `attrID` (`pcaID`),
  KEY `pcaID` (`pcaID`,`attrID`),
  CONSTRAINT `product_class_attributes_ibfk_10` FOREIGN KEY (`attrID`) REFERENCES `attributes` (`attrID`) ON UPDATE CASCADE,
  CONSTRAINT `product_class_attributes_ibfk_9` FOREIGN KEY (`pclsID`) REFERENCES `product_classes` (`pclsID`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci";

    public function __construct()
    {
        parent::__construct("product_class_attributes");
    }

}

?>