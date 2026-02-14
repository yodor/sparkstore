<?php
include_once("store/utils/PriceInfo.php");
include_once("store/utils/VariantItem.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/SellableDataParser.php");

include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductVariantsBean.php");
include_once("store/beans/SellableProducts.php");
include_once("objects/data/LinkedData.php");

class SellableDataParser
{
    protected $product_photos = null;
    protected $product_variants = null;
    protected $product_categories = null;

    public function __construct()
    {
        //$this->product_color_photos = new ProductColorPhotosBean();
        $this->product_photos = new ProductPhotosBean();
        $this->product_variants = new ProductVariantsBean();
        $this->product_categories = new ProductCategoriesBean();
    }

    /**
     * Populate sellable item properties using data from db result record
     * @param SellableItem $item
     * @param RawResult $result
     * @throws Exception
     */
    public function parse(SellableItem $item, RawResult $result) : void
    {

        $item->setProductID($result->get("prodID"));

        $catID = $result->get("catID");
        $item->setCategoryID($catID);

        //$category_result = $this->product_categories->getByID($catID, "category_name");
        $item->setCategoryName($result->get("category_name"));

        $parent_categories = $this->product_categories->getParentNodes($catID, array("category_name"));
        $categories = array();
        foreach ($parent_categories as $idx=>$category_result) {
            $categories[] = $category_result["category_name"];
        }

        $item->setCategoryPath($categories);

        if ($result->isSet("product_name")) {
            $item->setTitle($result->get("product_name"));
        }

        if ($result->isSet("brand_name")) {
            $item->setBrandName($result->get("brand_name"));
        }

        if ($result->isSet("model")) {
            $item->setModel($result->get("model"));
        }

        if ($result->isSet("class_name")) {
            $item->setClassName($result->get("class_name"));
        }

        if ($result->isSet("product_description")) {
            $item->setDescription($result->get("product_description"));
        }

        if ($result->isSet("seo_description")) {
            $item->setSeoDescription($result->get("seo_description"));
        }

        if ($result->isSet("keywords")) {
            $item->setKeywords($result->get("keywords"));
        }

        if ($result->isSet("stock_amount")) {
            $item->setStockAmount($result->get("stock_amount"));
        }

        if ($result->isSet("product_attributes")) {
            $attributes_text = $result->get("product_attributes");
            $pairs = explode("|", $attributes_text);
            foreach ($pairs as $pair) {
                if ($pair) {
                    list($name, $value) = explode(":", $pair);
                    $name = trim($name);
                    $value = trim($value);
                    $item->setAttribute($name, $value);
                }
            }
        }

        if ($result->isSet("product_variants")) {
            $variants_text = $result->get("product_variants");
            $pairs = explode("|", $variants_text);
            foreach ($pairs as $pair) {
                list($name, $value) = explode(":", $pair);
                $name = trim($name);
                $value = trim($value);
                $vitem = null;
                if ($item->haveVariant($name)) {
                    $vitem = $item->getVariant($name);
                }
                else {
                    $vitem = new VariantItem($name);
                }
                $vitem->addParameter($value);
                $query = $this->product_variants->queryVariantPhotos($item->getProductID(), $name, $value);
                $num = $query->exec();
                while ($photo = $query->nextResult()) {
                    $si = new StorageItem();
                    $si->className = "ProductVariantPhotosBean";
                    $si->id = $photo->get("pvpID");
                    $si->setType(StorageItem::TYPE_IMAGE);
                    $vitem->addGalleryItem($value, $si);
                }
                $item->setVariant($vitem);
            }
        }

        //sell price from productssql is set already to the promo price
        $priceInfo = new PriceInfo((float)$result->get("sell_price"), (float)$result->get("price"),  (int)$result->get("discount_percent"));
        $item->setPriceInfo($priceInfo);

        //attach default photo as single color gallery
        $qry = $this->product_photos->query("ppID");
        $qry->select->where()->add("prodID", $item->getProductID());
        $qry->select->order_by = " position ASC ";
        $num = $qry->exec();

        $main_photo = null;
        while ($row = $qry->next()) {

            $sitem = new StorageItem($row["ppID"], get_class($this->product_photos));
            $item->addGalleryItem($sitem);

            if (is_null($main_photo)) {
                $main_photo = $sitem;
            }
        }
        if ($main_photo instanceof StorageItem) {
            $item->setMainPhoto($main_photo);
        }

    }

    public function linkedData(SellableItem $item, ?URL $productURL=null) : LinkedData
    {

        $product = new LinkedData("Product");
        $product->set("sku", $item->getProductID());
        $product->set("name", $item->getTitle());
        $description = $item->getDescription();
        $seoDescription = $item->getSeoDescription();
        if ($seoDescription) $description = $seoDescription;
        $product->set("description", strip_tags($description));

        if (!is_null($productURL)) {
            $product->set("url", $productURL->fullURL());
        }
        $product->set("category", $item->getCategoryName());

        $product->set("brand", $item->getBrandName());

        $photos = $item->galleryItems();
        $urls = array();
        foreach ($photos as $si) {
            if ($si instanceof StorageItem) {
                $si->setName($item->getTitle());
                $urls[] = $si->hrefFull()->fullURL()->toString();
            }
        }
        $product->set("image", $urls);

        $offer = new LinkedData("Offer");
        $availability = "https://schema.org/OutOfStock";
        if ($item->getStockAmount()>0) {
            $availability = "https://schema.org/InStock";
        }
        $offer->set("availability", $availability);

        $offer->set("price", sprintf("%0.2f", $item->getPriceInfo()->getSellPrice()));
        $offer->set("priceCurrency", Spark::Get(StoreConfig::DEFAULT_CURRENCY));

        $priceValidUntil = date("Y-m-d", strtotime("+1 year"));
        $offer->set("priceValidUntil", $priceValidUntil);

        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $offerEUR = new LinkedData("Offer");
            $offerEUR->set("availability", $availability);
            $eurPrice = sprintf("%0.2f", ($item->getPriceInfo()->getSellPrice() / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE)));
            $offerEUR->set("price", $eurPrice);
            $offerEUR->set("priceCurrency", Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY));
            $offerEUR->set("priceValidUntil", $priceValidUntil);

            $product->setArray("offers", $offer->toArray(), $offerEUR->toArray());
        }
        else {
            $product->set("offers", $offer->toArray());
        }

        //TODO: multiple priceCurrency eg EUR/USD
        // $product->set("offers", array($offerEUR->toArray(), $offerUSD->toArray()));
        // or setArray using variadic parameters
        // $product->setArray("offers", $offer->toArray(), $offerEUR->toArray());


        return $product;

    }


}