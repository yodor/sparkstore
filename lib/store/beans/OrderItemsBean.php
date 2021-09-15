<?php
include_once("beans/DBTableBean.php");

class OrderItemsBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `order_items` (
 `itemID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `photo` longblob,
 `product` text NOT NULL,
 `qty` int(11) unsigned NOT NULL,
 `price` decimal(10,2) NOT NULL,
 `piID` int(11) unsigned DEFAULT NULL,
 `prodID` int(11) unsigned DEFAULT NULL,
 `position` int(11) unsigned NOT NULL,
 `orderID` int(11) unsigned NOT NULL,
 `auth_context` varchar(255) NOT NULL DEFAULT 'OrderOwnerAuthenticator',
 PRIMARY KEY (`itemID`),
 KEY `piID` (`piID`),
 KEY `position` (`position`),
 KEY `orderID` (`orderID`),
 KEY `prodID` (`prodID`),
 CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`piID`) REFERENCES `product_inventory` (`piID`) ON DELETE SET NULL ON UPDATE CASCADE,
 CONSTRAINT `order_items_ibfk_4` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8
";

    public function __construct()
    {
        parent::__construct("order_items");
    }

}

?>
