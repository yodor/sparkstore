<?php
include_once("sql/SQLSelect.php");
include_once("store/utils/CatalogParts.php");

/**
 * The select of the sellable products - the view sellable_products (SellableProducts), one row per visible product.
 * Built from small parts in order; a site changes one part by overriding its method and adds its own columns in
 * extend() (set with SellableProducts::SetProductsSelect() in the site init):
 *
 *  - base()       the product columns, its class and category
 *  - photo()      ppID - the first photo
 *  - promotion()  discount_percent - the best active promotion of the product's category (with its parent categories)
 *                 or of its sections (store_promos) - two joins of the active promotions
 *  - sellPrice()  sell_price - the promotion wins over the product's own promo price, never both
 *  - parts()      the subqueries of the enabled catalog parts (CatalogParts): sections, attributes, variants
 *  - extend()     empty - the site's columns and joins
 *
 * The view follows this select: SellableProducts recreates it when the select changes (a new version of the store or
 * the site class, a catalog part switched on or off).
 */
class ProductsSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        $this->base();
        $this->photo();
        $this->promotion();
        $this->sellPrice();
        $this->parts();
        $this->extend();
    }

    /**
     * The product columns, its class and category - visible products only
     * @return void
     */
    protected function base() : void
    {
        $this->columns(
            "p.prodID", "p.catID", "p.brand_name",
            "p.product_name", "p.product_description", "p.seo_title", "p.seo_description",
            "p.visible", "p.pclsID", "p.promo_price", "p.price",
            "p.insert_date", "p.update_date", "p.stock_amount"
        );
        $this->columns("pcls.class_name", "pc.category_name");

        $this->from("products p")
            ->leftJoin("product_classes pcls")->on("pcls.pclsID = p.pclsID")
            ->leftJoin("product_categories pc")->on("pc.catID = p.catID");

        $this->where()->expression("p.visible = 1");
    }

    /**
     * ppID - the first photo of the product
     * @return void
     */
    protected function photo() : void
    {
        $this->alias("(SELECT pp.ppID FROM product_photos pp WHERE pp.prodID = p.prodID ORDER BY pp.position ASC LIMIT 1)", "ppID");
    }

    /**
     * The best discount of the active promotions of the product (store_promos), 0 without one: a promotion of its
     * category or of a parent category, a promotion of one of its sections (the sections catalog part). Active from
     * the start date to the end date, both days included. The active promotions are resolved once per query in two
     * small derived tables joined by the category and by the product (promotion()) - one row per product, overlapping
     * promotions do not repeat it, the best one counts.
     * @return string the SQL expression
     */
    protected function discountExpression() : string
    {
        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            return "GREATEST(COALESCE(promo_category.discount, 0), COALESCE(promo_section.discount, 0))";
        }
        return "COALESCE(promo_category.discount, 0)";
    }

    /**
     * discount_percent - the joins of the active promotions: promo_category (catID - the promotions of a category
     * reach its sub categories), promo_section (prodID)
     * @return void
     */
    protected function promotion() : void
    {
        $this->from()->leftJoin("(SELECT pcc.catID, MAX(sp.discount_percent) AS discount FROM store_promos sp
            JOIN product_categories tc ON tc.catID = sp.targetID
            JOIN product_categories pcc ON pcc.lft BETWEEN tc.lft AND tc.rgt
            WHERE sp.target = 'Category' AND CURDATE() BETWEEN sp.start_date AND sp.end_date
            GROUP BY pcc.catID) promo_category")->on("promo_category.catID = p.catID");

        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            $this->from()->leftJoin("(SELECT ps.prodID, MAX(sp.discount_percent) AS discount FROM store_promos sp
            JOIN product_sections ps ON ps.secID = sp.targetID
            WHERE sp.target = 'Section' AND CURDATE() BETWEEN sp.start_date AND sp.end_date
            GROUP BY ps.prodID) promo_section")->on("promo_section.prodID = p.prodID");
        }

        $this->alias($this->discountExpression(), "discount_percent");
    }

    /**
     * sell_price: the price less the promotion discount, else the product's promo price, else its price - a
     * category or section promotion wins over the promo price (one discount, never two)
     * @return void
     */
    protected function sellPrice() : void
    {
        $discount = $this->discountExpression();
        $this->alias("IF($discount > 0, ROUND(p.price - (p.price * $discount / 100.0), 2),
            IF(p.promo_price > 0, p.promo_price, p.price))", "sell_price");
    }

    /**
     * The subqueries of the enabled catalog parts: product_sections (titles), product_attributes (name:value),
     * product_variants (option:value) - '|' separated
     * @return void
     */
    protected function parts() : void
    {
        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            $this->alias("(SELECT GROUP_CONCAT(s.section_title ORDER BY s.position ASC SEPARATOR '|')
            FROM product_sections ps JOIN sections s ON s.secID = ps.secID WHERE ps.prodID = p.prodID)", "product_sections");
        }

        if (CatalogParts::Enabled(CatalogParts::CLASSES)) {
            $this->alias("(SELECT GROUP_CONCAT(CONCAT(pa.attribute_name, ':', CAST(pa.attribute_value AS CHAR)) SEPARATOR '|')
            FROM product_attributes pa WHERE pa.prodID = p.prodID)", "product_attributes");
        }

        if (CatalogParts::Enabled(CatalogParts::VARIANTS)) {
            $this->alias("(SELECT GROUP_CONCAT(CONCAT(vo.option_name, ':', vo.option_value) ORDER BY vo.prodID, vo.pclsID ASC, vo.parentID ASC, vo.position ASC SEPARATOR '|')
            FROM product_variants pv LEFT JOIN variant_options vo ON vo.voID = pv.voID
            WHERE pv.prodID = p.prodID)", "product_variants");
        }
    }

    /**
     * The site's own columns and joins - empty here
     * @return void
     */
    protected function extend() : void
    {
    }
}
