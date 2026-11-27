<?php
include_once("beans/DBTableBean.php");

/**
 * The promotions: a discount in percent on a category (with its sub categories) or a section for a date span, both
 * days included (ProductsSQL::promotion). No foreign key - the target is a category or a section.
 */
class StorePromosBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `store_promos` (
  `spID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `target` enum('Category','Section') NOT NULL DEFAULT 'Category',
  `targetID` int(11) unsigned NOT NULL,
  `discount_percent` int(11) NOT NULL,
  PRIMARY KEY (`spID`),
  KEY `target` (`target`, `targetID`),
  KEY `dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    public function __construct()
    {
        parent::__construct("store_promos");
    }

}
