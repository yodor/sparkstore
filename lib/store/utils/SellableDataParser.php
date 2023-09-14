<?php
include_once("store/utils/PriceInfo.php");
include_once("store/utils/VariantItem.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/SellableDataParser.php");

include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductVariantsBean.php");
include_once("store/beans/SellableProducts.php");

class SellableDataParser
{
    protected $product_photos = null;
    protected $product_variants = null;

    public function __construct()
    {
        //$this->product_color_photos = new ProductColorPhotosBean();
        $this->product_photos = new ProductPhotosBean();
        $this->product_variants = new ProductVariantsBean();
    }

    /**
     * Populate sellable item properties using data from db result record
     * @param SellableItem $item
     * @param array $result
     * @throws Exception
     */
    public function parse(SellableItem $item, RawResult $result)
    {

        $item->setProductID($result->get("prodID"));

        $item->setCategoryID($result->get("catID"));

        if ($result->isSet("product_name")) {
            $item->setTitle($result->get("product_name"));
        }

        if ($result->isSet("brand_name")) {
            $item->setBrandName($result->get("brand_name"));
        }

        if ($result->isSet("model")) {
            $item->setModel($result->get("model"));
        }

        if ($result->isSet("product_description")) {
            $item->setDescription($result->get("product_description"));
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
                list($name, $value) = explode(":", $pair);
                $name = trim($name);
                $value = trim($value);
                $item->setAttribute($name, $value);
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

            if ($main_photo == null) {
                $main_photo = $sitem;
            }
        }

        $item->setMainPhoto($main_photo);

    }

}
?>