<?php
include_once("beans/OrderedDataBean.php");

class SectionsBean extends OrderedDataBean
{

    protected string $createString = "CREATE TABLE `sections` (
  `secID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `section_title` varchar(32) NOT NULL,
  `section_seodescription` text DEFAULT NULL,
  `position` int(11) NOT NULL,
  `home_visible` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`secID`),
  UNIQUE KEY `section_title` (`section_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci

";

    public function __construct()
    {
        parent::__construct("sections");
    }

}

?>
