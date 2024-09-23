<?php
include_once("beans/DBTableBean.php");

class InstockSubscribersBean extends DBTableBean
{

    protected string $createString = "CREATE TABLE `instock_subscribers` (
  `isID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `prodID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`isID`),
  UNIQUE KEY `email_product` (`email`,`prodID`) USING BTREE,
  KEY `email` (`email`),
  KEY `prodID` (`prodID`),
  CONSTRAINT `instock_subscribers_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci";

    public function __construct()
    {
        parent::__construct("instock_subscribers");

    }
}