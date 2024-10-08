<?php
include_once("beans/DBTableBean.php");

class AttributesBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `attributes` (
  `attrID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`attrID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    public function __construct()
    {
        parent::__construct("attributes");
    }

}

?>