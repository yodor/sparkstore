<?php
include_once("beans/DBTableBean.php");

class BackInstockProductsBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `backinstock_products` (
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
        Debug::ErrorLog("Updating back in stock list for prodID: $prodID");

        try {
            //INSERT INTO backinstock_products (prodID) VALUES ($prodID) ON DUPLICATE KEY UPDATE update_date=CURRENT_TIMESTAMP
            $insert = new SQLInsert();
            $insert->from = "backinstock_products";
            $insert->set("prodID", $prodID);
            $insert->on = " DUPLICATE KEY UPDATE update_date=CURRENT_TIMESTAMP ";
            $query = new DBQuery();
            $query->exec($insert);
            $query->free();

        } catch (Exception $e) {
            Debug::ErrorLog("Failed updating backinstock_products: ".$e->getMessage());
            throw $e;
        }
    }

    public function outofstock(int $prodID)
    {
        Debug::ErrorLog("Deleting from back in stock list for prodID: $prodID");

        try {
            $delete = new SQLDelete();
            $delete->from = "backinstock_products";
            $delete->where()->add("prodID", $prodID);
            $query = new DBQuery();
            $query->exec($delete);
            $query->free();
        } catch (Exception $e) {
            Debug::ErrorLog("Failed deleting from backinstock_products: ".$e->getMessage());
            throw $e;
        }
    }
}