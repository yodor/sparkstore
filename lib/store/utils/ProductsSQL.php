<?php
include_once("sql/SQLSelect.php");

class ProductsSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        $this->fields()->set(

            "p.prodID", "p.catID", "p.brand_name", "p.model",
             "p.product_name", "p.product_description", "p.seo_description",
            "p.visible", "p.pclsID", "p.promo_price", "p.price",
            "p.insert_date", "p.update_date", "p.stock_amount",
            "pvl.order_counter", "pvl.view_counter"

        );

        $this->fields()->setExpression("(
        SELECT pcls.class_name FROM product_classes pcls WHERE pcls.pclsID = p.pclsID LIMIT 1
        )", "class_name");

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

LEFT JOIN product_view_log pvl ON pvl.prodID = p.prodID


";

        $this->where()->add("p.visible", 1);
    }

    public function createView(string $view_name="sellable_products")
    {

        $sql = "CREATE VIEW IF NOT EXISTS $view_name AS ({$this->getSQL()})";
        $db = DBConnections::Open();
        $db->query($sql);

    }
}

?>