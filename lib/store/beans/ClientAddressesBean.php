<?php
include_once("beans/DBTableBean.php");

class ClientAddressesBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `client_addresses` (
 `uaID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `fullname` varchar(255)  NULL DEFAULT '',
 `phone` varchar(255)  NULL DEFAULT '',
 `city` varchar(255) NOT NULL,
 `postcode` varchar(255) NOT NULL,
 `address1` varchar(255) NOT NULL,
 `address2` varchar(255) DEFAULT NULL,
 `note` text,
 `userID` int(11) unsigned NOT NULL,
 PRIMARY KEY (`uaID`),
 UNIQUE KEY `userID` (`userID`),
 CONSTRAINT `client_addresses_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8
";

    public function __construct()
    {
        parent::__construct("client_addresses");
    }

}