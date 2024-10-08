<?php
include_once("beans/DBTableBean.php");

class OrdersBean extends DBTableBean
{
    const string STATUS_PROCESSING = "Processing";
    const string STATUS_SENT = "Sent";
    const string STATUS_COMPLETED = "Completed";
    const string STATUS_CANCELED = "Canceled";

    protected string $createString = "CREATE TABLE `orders` (
  `orderID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `total` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.0,
  `delivery_price` decimal(10,2) NOT NULL,
  `delivery_courier` int(11) NOT NULL,
  `delivery_option` int(11) NOT NULL,
  `delivery_address` text NOT NULL DEFAULT '',
  `note` varchar(512) NOT NULL,
  `require_invoice` tinyint(1) DEFAULT 0,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `completion_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('Processing','Sent','Completed','Canceled') NOT NULL DEFAULT 'Processing',
  `userID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`orderID`),
  KEY `userID` (`userID`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";


    public function __construct()
    {
        parent::__construct("orders");
    }

}

?>
