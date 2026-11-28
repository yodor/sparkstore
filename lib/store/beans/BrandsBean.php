<?php
include_once("beans/OrderedDataBean.php");

/**
 * The brands of the products (products.brand_name) - ordered by position (the admin list reorders them), a photo (the
 * logo), a description and an address
 */
class BrandsBean extends OrderedDataBean
{

    protected string $createString = "CREATE TABLE `brands` (
  `brandID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) NOT NULL,
  `home_visible` tinyint(1) DEFAULT 0,
  `summary` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `photo` longblob DEFAULT NULL,
  `position` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`brandID`),
  UNIQUE KEY `brand_name` (`brand_name`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci";

    public function __construct()
    {
        parent::__construct("brands");
    }

}
