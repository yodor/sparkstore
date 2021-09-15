<?php
include_once("sql/SQLSelect.php");

class ProductsSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        $this->fields()->set("ssz2.position AS sizing_position",
            "iav.value AS ia_value", "ca.attribute_name AS ia_name",
                             "pc.catID", "pc.category_name", "pp.ppID", "pi.piID", "pi.size_value",
                             "pi.color", "pi.pclrID",  "pi.prodID", "pi.stock_amount", "p.product_name",
                             "p.brand_name", "p.product_description", "p.long_description", "p.keywords",
                             "p.visible", "p.class_name", "p.section", "p.promo_price", "p.price",
                             "p.insert_date", "p.update_date", "sc.color_code",
            "pi.order_counter, pi.view_counter");

        //        $this->fields()->setExpression("(pclrs.color_photo IS NOT NULL)", "have_chip");

        $this->fields()->setExpression("(SELECT 
        GROUP_CONCAT(inventories.piID SEPARATOR '|') FROM 
        (SELECT pi1.piID, pclrpID, pi1.prodID FROM product_color_photos pcp 
        LEFT JOIN product_colors pc ON pc.pclrID=pcp.pclrID LEFT JOIN product_inventory pi1 ON pi1.pclrID=pc.pclrID GROUP BY pcp.pclrID ORDER BY pcp.pclrID DESC) inventories WHERE inventories.prodID=pi.prodID)", "inventory_ids");

        $this->fields()->setExpression("(SELECT 
        GROUP_CONCAT(DISTINCT(pi1.size_value) SEPARATOR '|') 
        FROM product_inventory pi1 
        WHERE pi1.prodID=pi.prodID AND (pi1.pclrID = pi.pclrID OR pi.pclrID IS NULL) 
        GROUP BY pi.pclrID )", "size_values");

        //series color id - same product all inventory color ids
        $this->fields()->setExpression("(SELECT 
        GROUP_CONCAT(DISTINCT(pi1.pclrID) SEPARATOR '|') 
        FROM product_inventory pi1 
        WHERE pi1.prodID=pi.prodID 
        ORDER BY pclrID DESC )", "color_ids");

        //series color photo id - same product all inventory color photo ids
        $this->fields()->setExpression("(SELECT 
        GROUP_CONCAT(inventory_photos.pclrpID SEPARATOR '|') FROM 
        (SELECT pclrpID, prodID FROM product_color_photos pcp 
        LEFT JOIN product_colors pc ON pc.pclrID=pcp.pclrID GROUP BY pcp.pclrID ORDER BY pcp.pclrID DESC) inventory_photos WHERE inventory_photos.prodID=pi.prodID )", "color_photo_ids");

        //series color name - same product all inventory color names
        $this->fields()->setExpression("(SELECT 
        GROUP_CONCAT(DISTINCT(pi1.color) SEPARATOR '|') 
        FROM product_inventory pi1 
        WHERE pi1.prodID=pi.prodID 
        ORDER BY pclrID DESC )", "color_names");

        //series color code - same product all inventory color codes
        $this->fields()->setExpression("(SELECT 
        GROUP_CONCAT(DISTINCT(sc4.color_code) SEPARATOR '|') 
        FROM product_inventory pi1 LEFT JOIN store_colors sc4 ON sc4.color=pi1.color
        WHERE pi1.prodID=pi.prodID 
        ORDER BY pclrID DESC )", "color_codes");

        //this item inventory attributes
        $this->fields()->setExpression("(SELECT 
        GROUP_CONCAT(DISTINCT(CONCAT(ca.attribute_name,':', cast(iav.value as char))) SEPARATOR '|') 
        FROM inventory_attribute_values iav JOIN class_attributes ca ON ca.caID = iav.caID 
        WHERE iav.piID = pi.piID)", "inventory_attributes");

        //this item color photo id
        $this->fields()->setExpression("(SELECT 
        pclrpID 
        FROM product_color_photos pcp 
        WHERE pcp.pclrID=pi.pclrID 
        ORDER BY position ASC LIMIT 1)", "pclrpID");

        $this->fields()->setExpression(
            "
            if (coalesce(sp.discount_percent,0)>0, pi.price - (pi.price * (coalesce(sp.discount_percent,0)) / 100.0), if(pi.promo_price>0, pi.promo_price, pi.price) )",
            "sell_price");
        //$this->fields()->setExpression("(SELECT min(pi4.price - (pi4.price * (coalesce(sp.discount_percent,0)) / 100.0)) FROM product_inventory pi4 WHERE pi4.prodID=pi.prodID )", "price_min");
        //$this->fields()->setExpression("(SELECT max(pi5.price - (pi5.price * (coalesce(sp.discount_percent,0)) / 100.0)) FROM product_inventory pi5 WHERE pi5.prodID=pi.prodID )", "price_max");

        $this->fields()->setExpression("coalesce(sp.discount_percent,0)", "discount_percent");



        $this->fields()->setExpression("(SELECT min(pi4.price - (pi4.price * (coalesce(sp.discount_percent,0)) / 100.0)) FROM product_inventory pi4 WHERE pi4.prodID=pi.prodID )", "price_min");
        $this->fields()->setExpression("(SELECT max(pi5.price - (pi5.price * (coalesce(sp.discount_percent,0)) / 100.0)) FROM product_inventory pi5 WHERE pi5.prodID=pi.prodID )", "price_max");


        //$this->fields()->setExpression("(SELECT ppID FROM product_photos pp WHERE pp.prodID = pi.prodID ORDER BY position ASC LIMIT 1)","ppID");

        $this->from = " product_inventory pi 

JOIN products p ON (p.prodID = pi.prodID AND p.visible=1) 
JOIN product_categories pc ON pc.catID=p.catID 

LEFT JOIN store_promos sp ON (sp.targetID = p.catID AND sp.target='Category' AND sp.start_date <= NOW() AND sp.end_date >= NOW()) 
LEFT JOIN product_colors pclrs ON pclrs.pclrID = pi.pclrID

LEFT JOIN inventory_attribute_values iav ON iav.piID=pi.piID 
LEFT JOIN class_attributes ca ON ca.caID=iav.caID 

LEFT JOIN store_colors sc ON sc.color=pi.color
LEFT JOIN product_photos pp ON pp.prodID=pi.prodID
LEFT JOIN store_sizes ssz2 ON ssz2.size_value=pi.size_value
";

    }
    public function createView(string $view_name="sellable_products")
    {

        $sql = "CREATE VIEW IF NOT EXISTS $view_name AS ({$this->getSQL()})";
        $db = DBConnections::Get();
        $res = $db->query($sql);
        $db->free($res);

    }
}

?>
