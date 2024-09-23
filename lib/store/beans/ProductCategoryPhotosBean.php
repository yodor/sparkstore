<?php
include_once("beans/OrderedDataBean.php");

class ProductCategoryPhotosBean extends OrderedDataBean
{
    protected string $createString = "CREATE TABLE `product_category_photos` (
  `pcpID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `photo` longblob NOT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `caption` text DEFAULT NULL,
  `catID` int(11) unsigned NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pcpID`),
  KEY `catID` (`catID`) USING BTREE,
  CONSTRAINT `product_category_photos_ibfk_1` FOREIGN KEY (`catID`) REFERENCES `product_categories` (`catID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct()
    {
        parent::__construct("product_category_photos");
    }

    //return ppID
    public function getFirstPhotoID(int $referenceID)
    {

        $resultID = -1;
        $qry = $this->queryField("catID", $referenceID, 1);
        $qry->select->order_by = " position ASC ";
        $qry->select->fields()->set($this->key());
        $qry->exec();

        if ($result = $qry->next()) {
            $resultID = $result[$this->key()];
        }

        return $resultID;

    }
}

?>
