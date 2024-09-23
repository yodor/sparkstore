<?php
include_once("beans/OrderedDataBean.php");

class SectionBannersBean extends OrderedDataBean
{
    protected string $createString = "CREATE TABLE `section_banners` (
 `sbID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `photo` longblob NOT NULL,
 `caption` text NOT NULL,
 `link` text NOT NULL,
 `position` int(11) NOT NULL,
 `date_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `secID` int(11) unsigned NOT NULL,
 PRIMARY KEY (`sbID`),
 KEY `secID` (`secID`),
 KEY `position` (`position`),
 CONSTRAINT `section_banners_ibfk_1` FOREIGN KEY (`secID`) REFERENCES `sections` (`secID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8
";

    public function __construct()
    {
        parent::__construct("section_banners");
    }

}

?>
