<?php
include_once("beans/DBTableBean.php");

class StorePromosBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `store_promos` (
  `spID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `targetID` int(11) unsigned NOT NULL,
  `discount_percent` int(11) NOT NULL,
  PRIMARY KEY (`spID`),
  KEY `targetID` (`targetID`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  CONSTRAINT `store_promos_ibfk_1` FOREIGN KEY (`targetID`) REFERENCES `product_categories` (`catID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci

";

    public function __construct()
    {
        parent::__construct("store_promos");
    }

}