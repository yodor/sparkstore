<?php
include_once("sql/SQLSelect.php");

class ProductsSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        $this->columns(

            "p.prodID", "p.catID", "p.brand_name",
             "p.product_name", "p.product_description", "p.seo_description",
            "p.visible", "p.pclsID", "p.promo_price", "p.price",
            "p.insert_date", "p.update_date", "p.stock_amount"
        );

        //this item sections
        $this->alias("(
        SELECT GROUP_CONCAT(s.section_title ORDER BY s.position ASC SEPARATOR '|') FROM product_sections ps JOIN sections s ON s.secID = ps.secID WHERE ps.prodID = p.prodID
        )", "product_sections");

        $this->alias("(SELECT 
        GROUP_CONCAT(CONCAT(pa.attribute_name,':',CAST(pa.attribute_value AS CHAR)) SEPARATOR '|')
        FROM product_attributes pa WHERE pa.prodID = p.prodID)", "product_attributes");

        //this item variants
        $this->alias("(SELECT 
        GROUP_CONCAT( CONCAT(vo.option_name,':', vo.option_value) ORDER BY vo.prodID, vo.pclsID ASC, vo.parentID ASC, vo.position ASC SEPARATOR '|') as variants
        FROM product_variants pv 
        LEFT JOIN variant_options vo ON vo.voID=pv.voID
        WHERE pv.prodID=p.prodID )", "product_variants");

        //this item photo
        $this->alias("(SELECT 
        pp.ppID 
        FROM product_photos pp 
        WHERE pp.prodID=p.prodID  
        ORDER BY position ASC LIMIT 1)", "ppID");

        $this->alias("(      
                if ( p.promo_price>0, p.promo_price, p.price - (p.price * (coalesce(sp.discount_percent, 0) / 100.0))  )
        )",
        "sell_price");

        $this->alias("coalesce(sp.discount_percent,0)", "discount_percent");

        $this->column("pc.category_name");
        $this->column("pcls.class_name");

        $this->from("products p")
            ->leftJoin("product_classes pcls")->on("pcls.pclsID = p.pclsID")
            ->leftJoin("product_categories pc")->on("pc.catID = p.catID")
            ->leftJoin("store_promos sp")
                ->on("( sp.targetID = p.catID AND sp.target='Category' AND NOW() BETWEEN sp.start_date AND sp.end_date )");


        $this->where()->expression("p.visible = 1");
//        echo $this->getSQL();

    }

}