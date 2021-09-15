<?php
include_once("beans/DBTableBean.php");

class InvoiceDetailsBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `invoice_details` (
 `ccID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `company_name` varchar(255) NOT NULL,
 `acc_person` varchar(255) NOT NULL,
 `city` varchar(255) NOT NULL,
 `postcode` varchar(255) NOT NULL,
 `address1` varchar(255) NOT NULL,
 `address2` varchar(255) DEFAULT NULL,
 `vat` varchar(255) NOT NULL,
 `vat_registered` tinyint(1) NULL DEFAULT '0',
 `userID` int(11) unsigned NOT NULL,
 PRIMARY KEY (`ccID`),
 UNIQUE KEY `userID` (`userID`),
 KEY `vat` (`vat`),
 CONSTRAINT `invoice_details_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8
";

    public function __construct()
    {
        parent::__construct("invoice_details");
    }

}

?>
