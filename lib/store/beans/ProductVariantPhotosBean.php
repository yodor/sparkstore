<?php
include_once("beans/OrderedDataBean.php");

class ProductVariantPhotosBean extends OrderedDataBean
{
    protected string $createString = "CREATE TABLE `product_variant_photos` (
  `pvpID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pvID` int(11) unsigned NOT NULL,
  `photo` longblob NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pvpID`),
  KEY `pvID` (`pvID`) USING BTREE,
  CONSTRAINT `product_variant_photos_ibfk_1` FOREIGN KEY (`pvID`) REFERENCES `product_variants` (`pvID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    //create trigger on this to delete from variant options of the same class
    public function __construct(?DBDriver $dbdriver = NULL)
    {
        parent::__construct("product_variant_photos", $dbdriver);
    }
}

?>