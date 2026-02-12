<?php
include_once("beans/DBTableBean.php");

class ProductSectionsBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `product_sections` (
  `psID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `secID` int(11) unsigned NOT NULL,
  `prodID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`psID`),
  UNIQUE KEY `secID_2` (`secID`,`prodID`),
  KEY `prodID` (`prodID`),
  KEY `secID` (`secID`),
  CONSTRAINT `product_sections_ibfk_1` FOREIGN KEY (`secID`) REFERENCES `sections` (`secID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_sections_ibfk_2` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    public function __construct()
    {
        parent::__construct("product_sections");
    }

}