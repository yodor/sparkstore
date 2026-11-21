<?php
include_once("beans/DBTableBean.php");

/**
 * The office of a courier the client chose (one per courier): its text and its key (CourierOffices)
 */
class CourierAddressesBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `courier_addresses` (
 `eoID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `office` varchar(255) NOT NULL,
 `userID` int(11) unsigned NOT NULL,
 `courier` int(11) unsigned NOT NULL DEFAULT 0,
 `office_key` varchar(32) DEFAULT NULL,
 PRIMARY KEY (`eoID`),
 UNIQUE KEY `user_courier` (`userID`, `courier`),
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