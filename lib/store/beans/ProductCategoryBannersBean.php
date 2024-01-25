<?php
include_once("beans/OrderedDataBean.php");

class ProductCategoryBannersBean extends OrderedDataBean
{
    protected $createString = "CREATE TABLE `product_category_banners` (
 `pcbbID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `photo` longblob NOT NULL,
 `caption` text NOT NULL,
 `link` text NOT NULL,
 `position` int(11) NOT NULL,
 `date_upload` timestamp NOT NULL DEFAULT current_timestamp(),
 `catID` int(11) unsigned NOT NULL,
 PRIMARY KEY (`pcbbID`),
 KEY `catID` (`catID`),
 KEY `position` (`position`),
 CONSTRAINT `product_category_banners_ibfk_1` FOREIGN KEY (`catID`) REFERENCES `product_categories` (`catID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

    public function __construct()
    {
        parent::__construct("product_category_banners");
    }

}

?>
