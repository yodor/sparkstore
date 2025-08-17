<?php
include_once("beans/DBTableBean.php");

class ProductsBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `products` (
  `prodID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catID` int(11) unsigned NOT NULL,
  `brand_name` varchar(255) DEFAULT 'maxmotors',
  `pclsID` int(11) unsigned DEFAULT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `subtitle` text DEFAULT NULL,
  `product_description` text DEFAULT NULL,
  `seo_description` varchar(512) DEFAULT NULL,
  `model` varchar(512) DEFAULT '''''',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `buy_price` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `promo_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `weight` decimal(10,3) unsigned NOT NULL DEFAULT 0.000,
  `stock_amount` int(11) DEFAULT 0,
  `visible` tinyint(1) DEFAULT 0,
  `importID` int(11) unsigned DEFAULT NULL,
  `insert_date` datetime NOT NULL,
  `update_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `order_counter` int(11) unsigned DEFAULT 0,
  `view_counter` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`prodID`),
  KEY `catID` (`catID`),
  KEY `importID` (`importID`),
  KEY `brand_name` (`brand_name`),
  KEY `update_date` (`update_date`),
  KEY `insert_date` (`insert_date`),
  KEY `visible` (`visible`),
  KEY `pclsID` (`pclsID`),
  CONSTRAINT `products_ibfk_4` FOREIGN KEY (`brand_name`) REFERENCES `brands` (`brand_name`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_5` FOREIGN KEY (`catID`) REFERENCES `product_categories` (`catID`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_6` FOREIGN KEY (`pclsID`) REFERENCES `product_classes` (`pclsID`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci";

    public function __construct()
    {
        parent::__construct("products");
    }

}

?>