<?php
include_once("beans/OrderedDataBean.php");

class ProductPhotosBean extends OrderedDataBean
{
    protected string $createString = "CREATE TABLE `product_photos` (
 `ppID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `photo` longblob NOT NULL,
 `prodID` int(11) unsigned NOT NULL,
 `date_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `caption` varchar(255) DEFAULT NULL,
 `position` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`ppID`),
 KEY `prodID` (`prodID`),
 CONSTRAINT `product_photos_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("product_photos");
    }

    //return ppID
    public function getFirstPhotoID(int $prodID)
    {

        $ppID = -1;
        $qry = $this->queryField("prodID", $prodID, 1);
        $qry->select->order_by = " position ASC ";
        $qry->select->fields()->set($this->key());
        $qry->exec();

        if ($photo_row = $qry->next()) {
            $ppID = $photo_row[$this->key()];
        }

        return $ppID;

    }
}