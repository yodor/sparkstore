<?php
include_once("beans/OrderedDataBean.php");

class GalleryPhotosBean extends OrderedDataBean
{

    protected $createString = "CREATE TABLE `gallery_photos` (
 `gpID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `photo` longblob NOT NULL,
 `date_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `caption` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
 `position` int(11) NOT NULL,
 PRIMARY KEY (`gpID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("gallery_photos");
    }

}

?>
