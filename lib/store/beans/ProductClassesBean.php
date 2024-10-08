<?php
include_once("beans/DBTableBean.php");

class ProductClassesBean extends DBTableBean
{

    protected string $createString = "CREATE TABLE `product_classes` (
  `pclsID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL,
  PRIMARY KEY (`pclsID`),
  UNIQUE KEY `class_name` (`class_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
";

    public function __construct()
    {
        parent::__construct("product_classes");
    }

}

?>