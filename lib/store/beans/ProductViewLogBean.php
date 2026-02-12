<?php
include_once("beans/DBTableBean.php");

class ProductViewLogBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `product_view_log` (
  `prodID` int(11) unsigned NOT NULL,
  `view_counter` int(11) unsigned NOT NULL,
  `order_counter` int(11) unsigned NOT NULL,
  `update_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `prodID` (`prodID`),
  KEY `update_date` (`update_date`),
  KEY `view_counter` (`view_counter`),
  KEY `prodID_2` (`prodID`,`view_counter`),
  KEY `prodID_3` (`prodID`,`order_counter`),
  CONSTRAINT `product_view_log_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci

";
    public function __construct(?DBDriver $dbdriver = NULL)
    {
        parent::__construct("product_view_log", $dbdriver);
    }
}