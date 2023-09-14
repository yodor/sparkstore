<?php
include_once("beans/DBTableBean.php");

class BackInstockProductsBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `backinstock_products` (
  `bispID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prodID` int(11) unsigned NOT NULL,
  `update_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`bispID`),
  UNIQUE KEY `prodID` (`prodID`),
  CONSTRAINT `backinstock_products_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    public function __construct()
    {
        parent::__construct("backinstock_products");
    }

    public function backinstock(int $prodID)
    {
        //insert into backinstock list
        debug("Updating back in stock list for prodID: $prodID");
        $db = DBConnections::Get();
        try {
            $db->transaction();
            if (!$db->query("INSERT INTO backinstock_products (prodID) VALUES ($prodID) ON DUPLICATE KEY UPDATE update_date=CURRENT_TIMESTAMP")) {
                throw new Exception($db->getError());
            }
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
    public function outofstock(int $prodID)
    {
        debug("Deleting from back in stock list for prodID: $prodID");

        $db = DBConnections::Get();
        try {
            $db->transaction();
            if (!$db->query("DELETE FROM backinstock_products WHERE prodID = $prodID")) {
                throw new Exception($db->getError());
            }
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}

?>