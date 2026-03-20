<?php

class ProductVariantsBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `product_variants` (
  `pvID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prodID` int(11) unsigned NOT NULL,
  `voID` int(11) unsigned NOT NULL,
  `variant_price` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`pvID`),
  UNIQUE KEY `prodID_2` (`prodID`,`voID`),
  KEY `voID` (`voID`),
  KEY `prodID` (`prodID`) USING BTREE,
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `products` (`prodID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_variants_ibfk_2` FOREIGN KEY (`voID`) REFERENCES `variant_options` (`voID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    public function __construct(?DBDriver $dbdriver = NULL)
    {
        parent::__construct("product_variants", $dbdriver);
    }

    public function queryProduct(int $prodID) : SelectQuery
    {

        $query = $this->queryFull();
        $query->stmt->reset();
        $query->stmt->set("pv.pvID", "pv.prodID", "pv.variant_price", "vo.voID", "vo.option_name", "vo.option_value", "vo.parentID", "vo.position");
        $query->stmt->setAliasExpression("(SELECT position FROM variant_options vo1 WHERE vo1.voID = vo.parentID)", "parent_position");
        $query->stmt->setAliasExpression("(SELECT pclsID FROM variant_options vo1 WHERE vo1.voID = vo.parentID)", "parent_class");
        $query->stmt->setAliasExpression("(SELECT prodID FROM variant_options vo1 WHERE vo1.voID = vo.parentID)", "parent_product");

        $query->stmt->from("product_variants pv")->join("variant_options vo")->on("vo.voID = pv.voID");
        $query->stmt->where()->add("pv.prodID", $prodID);
        $query->stmt->orderList("parent_product, parent_class, parentID, parent_position, position");
        return $query;
    }

    public function queryVariantPhotos(int $prodID, string $option_name, string $option_value) : SelectQuery
    {
        $query = $this->queryProduct($prodID);
        $query->stmt->set("pvp.pvpID");
        $query->stmt->where()->add("option_name", $option_name);
        $query->stmt->where()->add("option_value", $option_value);
        $query->stmt->from()->join("product_variant_photos pvp")->on("pvp.pvID = pv.pvID ");
        return $query;
    }
}