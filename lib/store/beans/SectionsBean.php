<?php
include_once("beans/OrderedDataBean.php");

class SectionsBean extends OrderedDataBean
{

    protected string $createString = "CREATE TABLE `sections` (
 `secID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `section_title` varchar(32) NOT NULL,
 `position` int(11) NOT NULL,
 PRIMARY KEY (`secID`),
 UNIQUE KEY `section_title` (`section_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("sections");
    }

}

?>
