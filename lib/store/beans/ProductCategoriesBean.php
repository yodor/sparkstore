<?php
include_once("beans/NestedSetBean.php");

class ProductCategoriesBean extends NestedSetBean
{

    protected string $createString = "CREATE TABLE `product_categories` (
  `catID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  `parentID` int(11) unsigned NOT NULL DEFAULT 0,
  `lft` int(11) unsigned NOT NULL,
  `rgt` int(11) unsigned NOT NULL,
  `category_seotitle` text DEFAULT NULL,
  `category_seodescription` varchar(512) DEFAULT NULL,
  `category_description` text DEFAULT NULL,
  PRIMARY KEY (`catID`),
  KEY `category_name` (`category_name`),
  KEY `parentID` (`parentID`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
";

    public function __construct()
    {
        parent::__construct("product_categories");
    }

}