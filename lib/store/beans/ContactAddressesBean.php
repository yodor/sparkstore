<?php
include_once("beans/OrderedDataBean.php");

class ContactAddressesBean extends OrderedDataBean
{
    protected string $createString = "CREATE TABLE `contact_addresses` (
 `caID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `city` varchar(255) NOT NULL DEFAULT '',
 `address` text NOT NULL DEFAULT '',
 `phone` varchar(255) DEFAULT '',
 `email` varchar(255) DEFAULT '',
 `map_url` text NOT NULL DEFAULT '',
 `position` int not null default 0,
 PRIMARY KEY (`caID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("contact_addresses");
    }

}

?>