<?php
include_once("beans/OrderedDataBean.php");

class ProductColorPhotosBean extends OrderedDataBean
{
    protected $createString = "CREATE TABLE `product_color_photos` (
 `pclrpID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `photo` longblob NOT NULL,
 `pclrID` int(11) unsigned NOT NULL,
 `date_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `position` int(11) NOT NULL DEFAULT '0',
 `caption` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`pclrpID`),
 KEY `pclrID` (`pclrID`),
 CONSTRAINT `product_color_photos_ibfk_1` FOREIGN KEY (`pclrID`) REFERENCES `product_colors` (`pclrID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("product_color_photos");
    }

    //return pclrpID
    public function getFirstPhotoID(int $pclrID)
    {
        $pclrpID = -1;

        $qry = $this->queryField("pclrID", $pclrID);
        $qry->select->fields()->set($this->key());
        $qry->select->order_by = " position ASC ";
        $qry->select->limit = " 1 ";

        if ($qry->exec() && $photo_row = $qry->next()) {
            $pclrpID = $photo_row[$this->key()];
        }

        return $pclrpID;

    }

}

?>
