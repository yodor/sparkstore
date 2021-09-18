<?php
include_once("store/utils/PriceInfo.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/SellableDataParser.php");
include_once("store/beans/ProductColorPhotosBean.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/SellableProducts.php");

class SellableDataParser
{
    protected $product_photos = null;
    protected $product_color_photos = null;

    public function __construct()
    {

        $this->product_color_photos = new ProductColorPhotosBean();
        $this->product_photos = new ProductPhotosBean();

    }

    /**
     * Populate sellable item properties using data from db result record
     * @param SellableItem $item
     * @param array $result
     * @throws Exception
     */
    public function parse(SellableItem $item, RawResult $result)
    {

        $piID = $result->get("piID");
        if ($item->getActiveInventoryID()<1) $item->setInventoryID($piID);

        $item->setRawResult($piID, $result);

        if ($result->isSet("product_name")) {
            $item->setTitle($result->get("product_name"));
        }
        if ($result->isSet("product_description")) {
            $item->setCaption($result->get("product_description"));
        }
        if ($result->isSet("long_description")) {
            $item->setDescription($result->get("long_description"));
        }
        if ($result->isSet("keywords")) {
            $item->setKeywords($result->get("keywords"));
        }

        $attr_all = SellableProducts::ParseAttributes($result->get("inventory_attributes"));

        $item->setAttributes($piID, $attr_all);

        $pclrID = (int)$result->get("pclrID");
        $piID = (int)$result->get("piID");

        $size_value = "".$result->get("size_value");

//        var_dump($result);
//        echo "<HR>";

        //sell price from productssql is set already to the promo price
        $priceInfo = new PriceInfo((float)$result->get("sell_price"), (float)$result->get("price"), (int)$result->get("stock_amount"), (int)$result->get("discount_percent"));

        $item->setSizeValue($piID, $size_value);

        $item->setPriceInfo($piID, $priceInfo);

        $item->setColorID($piID, $pclrID);


        if ($result->isSet("color")) {
            $item->setColorName($pclrID, $result->get("color"));
        }

        if ($result->isSet("color_code")) {
            $item->setColorCode($pclrID, $result->get("color_code"));
        }

        if (!$item->haveGalleryItems($pclrID)) {

            //use product photos instead of product color photos
            $use_photos = FALSE;

            if ($pclrID > 0) {
                $qry = $this->product_color_photos->query("pclrpID");
                $qry->select->where()->add("pclrID", $pclrID);
                $qry->select->order_by = " position ASC ";
                $num = $qry->exec();
                if ($num < 1) $use_photos = TRUE;
                while ($row = $qry->next()) {
                    $sitem = new StorageItem($row["pclrpID"], get_class($this->product_color_photos));
                    $item->addGalleryItem($pclrID, $sitem);
                }

            }
            if ($use_photos || $pclrID < 1) {
                //attach default photo as single color gallery
                $qry = $this->product_photos->query("ppID");
                $qry->select->where()->add("prodID", $item->getProductID());
                $qry->select->order_by = " position ASC ";
                $num = $qry->exec();

                while ($row = $qry->next()) {
                    $sitem = new StorageItem($row["ppID"], get_class($this->product_photos));
                    $item->addGalleryItem($pclrID, $sitem);
                }
            }
        }

        //use the color chip from product color scheme
        if ($result->isSet("have_chip") && (int)$result->get("have_chip") > 0) {
            $sitem = new StorageItem($pclrID,  "ProductColorsBean", "color_photo");
            $item->setColorChip($pclrID, $sitem);
        }
        else {
            //no chip assigned - use first image from the gallery if there is at least one coloring scheme setup
            $gallery_items = $item->galleryItems($pclrID);
            if (isset($gallery_items[0])) {
                $item->setColorChip($pclrID, $gallery_items[0]);
            }
            else {
                //use the color code as color button
                $item->setColorChip($pclrID, null);
            }
        }


    }

    public function processMainPhoto(SellableItem $item)
    {

        $activeID = $item->getActiveInventoryID();

        if ($activeID>0) {

            $pclrID = $item->getColorID($activeID);
            if ($item->haveGalleryItems($pclrID)) {
                $gallery_items = $item->galleryItems($pclrID);
                $item->setMainPhoto($gallery_items[0]);
            }


        }
        else {
            $photos_query = $this->product_photos->queryField("prodID", $item->getProductID(), 0, "ppID");
            $photos_query->select->order_by = "position ASC";
            $photos_query->select->limit = " 1 ";
            $num = $photos_query->exec();
            if ($photo_row = $photos_query->next()) {

                $main_photo = new StorageItem($photo_row["ppID"], "ProductPhotosBean");
                $item->setMainPhoto($main_photo);
                // $this->gallery_href = STORAGE_LOCAL . "?cmd=image&height=648&width=648";

                // $this->big_href = STORAGE_LOCAL . "?cmd=image&class=ProductPhotosBean&id=$this->photoID&height=648&width=648";
                //$big_href = STORAGE_LOCAL . "?cmd=image";

            }
        }

    }
}
?>