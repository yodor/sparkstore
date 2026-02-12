<?php
include_once("beans/DBTableBean.php");

class CourierAddressesBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `courier_addresses` (
 `eoID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `office` varchar(255) NOT NULL,
 `userID` int(11) unsigned NOT NULL,
 PRIMARY KEY (`eoID`),
 UNIQUE KEY `userID` (`userID`) USING BTREE,
 CONSTRAINT `courier_addresses_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

    /**
     * CourierAddressesBean
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct("courier_addresses");
    }

}