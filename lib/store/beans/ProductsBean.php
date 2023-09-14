<?php
include_once("beans/DBTableBean.php");

class ProductsBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `products` (
  `prodID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catID` int(11) unsigned NOT NULL,
  `pclsID` int(11) unsigned DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `model` varchar(512) DEFAULT '',
  `product_description` text DEFAULT NULL,
  `keywords` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `promo_price` decimal(10,2) DEFAULT 0.00,
  `stock_amount` int(11) NOT NULL,
  `visible` tinyint(1) DEFAULT 0,
  `importID` int(11) unsigned DEFAULT NULL,
  `update_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `insert_date` datetime NOT NULL,
  PRIMARY KEY (`prodID`),
  UNIQUE KEY `importID` (`importID`) USING BTREE,
  KEY `catID` (`catID`),
  KEY `brand_name` (`brand_name`),
  KEY `update_date` (`update_date`),
  KEY `insert_date` (`insert_date`),
  KEY `visible` (`visible`),
  KEY `pclsID` (`pclsID`),
  CONSTRAINT `products_ibfk_4` FOREIGN KEY (`brand_name`) REFERENCES `brands` (`brand_name`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_5` FOREIGN KEY (`catID`) REFERENCES `product_categories` (`catID`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_8` FOREIGN KEY (`pclsID`) REFERENCES `product_classes` (`pclsID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12891 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci";

    public function __construct()
    {
        parent::__construct("products");
    }

}

?>