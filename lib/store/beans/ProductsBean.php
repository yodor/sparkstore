<?php
include_once("beans/DBTableBean.php");

class ProductsBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `products` (
  `prodID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catID` int(11) unsigned NOT NULL,
  `section` varchar(255) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `brand_name` varchar(255) NOT NULL,
  `product_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `product_description` text DEFAULT NULL,
  `long_description` text DEFAULT NULL,
  `keywords` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `buy_price` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `weight` decimal(10,3) unsigned NOT NULL DEFAULT 0.000,
  `promo_price` decimal(10,2) DEFAULT 0.00,
  `visible` tinyint(1) DEFAULT 0,
  `importID` int(11) unsigned DEFAULT NULL,
  `update_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `insert_date` datetime NOT NULL,
  PRIMARY KEY (`prodID`),
  KEY `catID` (`catID`),
  KEY `importID` (`importID`),
  KEY `gender` (`section`),
  KEY `brand_name` (`brand_name`),
  KEY `update_date` (`update_date`),
  KEY `insert_date` (`insert_date`),
  KEY `visible` (`visible`),
  KEY `class_name` (`class_name`),
  CONSTRAINT `products_ibfk_4` FOREIGN KEY (`brand_name`) REFERENCES `brands` (`brand_name`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_5` FOREIGN KEY (`catID`) REFERENCES `product_categories` (`catID`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_6` FOREIGN KEY (`class_name`) REFERENCES `product_classes` (`class_name`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_7` FOREIGN KEY (`section`) REFERENCES `sections` (`section_title`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("products");
    }

}

?>
