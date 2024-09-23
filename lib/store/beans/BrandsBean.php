<?php
include_once("beans/DBTableBean.php");

class BrandsBean extends DBTableBean
{

    protected string $createString = "CREATE TABLE `brands` (
  `brandID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) NOT NULL,
  `home_visible` tinyint(1) DEFAULT 0,
  `summary` text DEFAULT '',
  `url` varchar(255) DEFAULT NULL,
  `photo` longblob DEFAULT NULL,
  PRIMARY KEY (`brandID`),
  UNIQUE KEY `brand_name` (`brand_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    public function __construct()
    {
        parent::__construct("brands");
    }

}

?>