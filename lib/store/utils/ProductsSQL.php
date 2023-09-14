<?php
include_once("sql/SQLSelect.php");

class ProductsSQL extends SQLSelect
{
    //create view class_attributes as (SELECT pca.pcaID, pc.pclsID, pc.class_name, a.attrID, a.name FROM product_class_attributes pca JOIN product_classes pc ON pc.pclsID = pca.pclsID JOIN attributes a ON a.attrID=pca.attrID)
    public function __construct()
    {
        parent::__construct();

        $this->fields()->set(

            "p.prodID", "p.catID", "p.brand_name", "p.model",
             "p.product_name", "p.product_description", "p.keywords",
            "p.visible", "p.pclsID", "p.promo_price", "p.price",
            "p.insert_date", "p.update_date", "p.stock_amount",
            "p.order_counter", "p.view_counter"

        );

        $this->fields()->setExpression("(
        SELECT pc.category_name FROM product_categories pc WHERE pc.catID = p.catID LIMIT 1
        )", "category_name");

        //this item sections
        $this->fields()->setExpression("(
        SELECT GROUP_CONCAT(s.section_title ORDER BY s.position ASC SEPARATOR '|') FROM product_sections ps JOIN sections s ON s.secID = ps.secID WHERE ps.prodID = p.prodID
        )", "product_sections");


        //this item attributes
        $this->fields()->setExpression("(SELECT 
        GROUP_CONCAT(CONCAT(a.name,':', cast(pcav.value as char)) ORDER BY pca.pcaID ASC SEPARATOR '|') 
        FROM product_class_attribute_values pcav 
        JOIN product_class_attributes pca ON pca.pcaID = pcav.pcaID 
        JOIN attributes a ON a.attrID=pca.attrID 
        WHERE pcav.prodID = p.prodID)", "product_attributes");

        //this item variants
        $this->fields()->setExpression("(SELECT 
        group_concat( CONCAT(vo.option_name,':', vo.option_value) ORDER BY vo.prodID, vo.pclsID ASC, vo.parentID ASC, vo.position ASC SEPARATOR '|') as variants
        FROM product_variants pv 
        LEFT JOIN variant_options vo ON vo.voID=pv.voID
        WHERE pv.prodID=p.prodID )", "product_variants");

        //this item photo
        $this->fields()->setExpression("(SELECT 
        pp.ppID 
        FROM product_photos pp 
        WHERE pp.prodID=p.prodID  
        ORDER BY position ASC LIMIT 1)", "ppID");

        $this->fields()->setExpression("(      
                if ( p.promo_price>0, p.promo_price, p.price - (coalesce(sp.discount_percent, 0) / 100.0)  )
        )",
        "sell_price");

        $this->fields()->setExpression("coalesce(sp.discount_percent,0)", "discount_percent");


        $this->from = " products p  

LEFT JOIN store_promos sp 
ON ( sp.targetID = p.catID AND sp.target='Category' AND (sp.start_date <= NOW() AND sp.end_date >= NOW()) ) 



";
        //LEFT JOIN product_class_attribute_values pcav ON pcav.prodID=p.prodID
        //LEFT JOIN product_class_attributes pca ON pca.pcaID=pcav.pcaID
        //LEFT JOIN attributes a ON a.attrID = pca.attrID
        $this->where()->add("p.visible", 1);
    }
    public function createView(string $view_name="sellable_products")
    {

        $sql = "CREATE VIEW IF NOT EXISTS $view_name AS ({$this->getSQL()})";
        $db = DBConnections::Get();
        $res = $db->query($sql);
        $db->free($res);

        //SELECT p.*, group_concat(concat(vo.option_name,':', vo.option_value) SEPARATOR '|') as variants FROM `sellable_products` p
        // LEFT JOIN product_variants pv ON pv.prodID=p.prodID
        // LEFT JOIN variant_options vo ON vo.voID=pv.voID
        // GROUP BY p.prodID ORDER BY `variants` DESC

    }
}

?>