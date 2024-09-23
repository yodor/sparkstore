<?php
include_once("beans/OrderedDataBean.php");

class VariantOptionsBean extends OrderedDataBean
{
    protected string $createString = "CREATE TABLE `variant_options` (
  `voID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parentID` int(11) unsigned DEFAULT NULL,
  `option_name` varchar(255) NOT NULL,
  `option_value` varchar(255) DEFAULT NULL,
  `pclsID` int(11) unsigned DEFAULT NULL,
  `prodID` int(11) unsigned DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`voID`),
  UNIQUE KEY `name_value` (`option_name`,`option_value`,`pclsID`,`prodID`) USING BTREE,
  KEY `parentID` (`parentID`),
  KEY `pclsID` (`pclsID`),
  KEY `prodID` (`prodID`),
  CONSTRAINT `variant_options_ibfk_1` FOREIGN KEY (`parentID`) REFERENCES `variant_options` (`voID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `variant_options_ibfk_2` FOREIGN KEY (`pclsID`) REFERENCES `product_classes` (`pclsID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `variant_options_ibfk_3` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci

";

    public function __construct()
    {
        parent::__construct("variant_options");
    }


}

?>