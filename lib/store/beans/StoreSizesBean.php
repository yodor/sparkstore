<?php
include_once("beans/OrderedDataBean.php");

class StoreSizesBean extends OrderedDataBean
{
    protected $createString = "CREATE TABLE `store_sizes` (
 `pszID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `size_value` varchar(255) NOT NULL,
 `position` INT,
 PRIMARY KEY (`pszID`),
 UNIQUE KEY `size_value` (`size_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("store_sizes");
    }

}

?>