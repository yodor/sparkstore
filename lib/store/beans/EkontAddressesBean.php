<?php
include_once("beans/DBTableBean.php");

class EkontAddressesBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `ekont_addresses` (
 `eoID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `office` varchar(255) NOT NULL,
 `userID` int(11) unsigned NOT NULL,
 PRIMARY KEY (`eoID`),
 UNIQUE KEY `userID` (`userID`) USING BTREE,
 CONSTRAINT `ekont_addresses_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8
";

    /**
     * EkontAddressesBean constructor.
     * Preferred users' 'Ekont' address
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct("ekont_addresses");
    }

}

?>
