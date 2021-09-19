<?php
include_once("store/utils/ProductsSQL.php");

class ProductPricesSQL extends ProductsSQL
{
    public function __construct()
    {
        parent::__construct();

        $this->fields()->reset();
        $this->fields()->set("pi.piID", "p.prodID", "p.promo_price", "p.price");
        $this->fields()->setExpression(
            "
            if (coalesce(sp.discount_percent,0)>0, pi.price - (pi.price * (coalesce(sp.discount_percent,0)) / 100.0), if(pi.promo_price>0, pi.promo_price, pi.price) )",
            "sell_price");
        //$this->fields()->setExpression("(SELECT min(pi4.price - (pi4.price * (coalesce(sp.discount_percent,0)) / 100.0)) FROM product_inventory pi4 WHERE pi4.prodID=pi.prodID )", "price_min");
        //$this->fields()->setExpression("(SELECT max(pi5.price - (pi5.price * (coalesce(sp.discount_percent,0)) / 100.0)) FROM product_inventory pi5 WHERE pi5.prodID=pi.prodID )", "price_max");

        $this->fields()->setExpression("coalesce(sp.discount_percent,0)", "discount_percent");
    }
}
