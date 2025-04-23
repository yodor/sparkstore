<?php
include_once("responders/RequestResponder.php");
include_once("components/Action.php");
include_once("storage/StorageItem.php");

include_once("store/beans/SellableProducts.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/ProductsSQL.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("storage/SparkHTTPResponse.php");

class DownloadCSVProducts extends RequestResponder
{

    const string FILENAME = "catalog_products.csv";


    const string TYPE_FACEBOOK = "facebook";
    const string TYPE_GOOGLE = "google";
    const string TYPE_GOOGLE_MERCHANT = "google_merchant";
    const string TYPE_FULL = "full";
    const string TYPE_IMAGES = "images";

    protected array $supported_content = array();
    protected string $type = "";

    protected array $keys = array();

    public function __construct()
    {
        parent::__construct();

        $this->supported_content[] = self::TYPE_FACEBOOK;
        $this->supported_content[] = self::TYPE_GOOGLE;
        $this->supported_content[] = self::TYPE_GOOGLE_MERCHANT;
        $this->supported_content[] = self::TYPE_FULL;
        $this->supported_content[] = self::TYPE_IMAGES;
    }

    public function createAction(string $title = ""): ?Action
    {
        $type = "";
        if (strcmp($title, self::TYPE_FACEBOOK) == 0) {
            $type = self::TYPE_FACEBOOK;
        } else if (strcmp($title, self::TYPE_GOOGLE) == 0) {
            $type = self::TYPE_GOOGLE;
        } else if (strcmp($title, self::TYPE_GOOGLE_MERCHANT) == 0) {
            $type = self::TYPE_GOOGLE_MERCHANT;
        } else if (strcmp($title, self::TYPE_FULL) == 0) {
            $type = self::TYPE_FULL;
        } else if (strcmp($title, self::TYPE_IMAGES) == 0) {
            $type = self::TYPE_IMAGES;
        }

        $action = parent::createAction($title);
        $action->getURL()->add(new URLParameter("type", $type));
        $action->setTooltip("Download CSV - " . $type);
        return $action;
    }

    protected function exportImages() : void
    {

        //ini_set('max_execution_time', 300);

        $folder = CACHE_PATH."/catalog-images-".time();
        if (!mkdir($folder)) {
            throw new Exception("Can not create export folder");
        }

        $select = new SQLSelect();
        $select->from = " product_photos  ";
        $select->fields()->set("ppID");

        $select->order_by = " ppID ASC ";
        $qry = new SQLQuery($select);
        $num = $qry->exec();

        while ($result = $qry->nextResult()) {
            $ppID = $result->get("ppID");

            $select1 = new SQLSelect();
            $select1->from = " product_photos ";
            $select1->fields()->set("photo");
            $select1->where()->add("ppID", $ppID);
            $qry1 = new SQLQuery($select1);
            $num1 = $qry1->exec();
            if ($result1 = $qry1->nextResult()) {
                $photo = $result1->get("photo");
                $photo = unserialize($photo);
                if ($photo instanceof ImageStorageObject) {
                    $current = new SparkFile();
                    $current->setPath($folder);
                    $current->setFilename($ppID);
                    $current->open("w");
                    $current->write($photo->data());
                    $current->close();
                }
//                $photo = null;
//                $result1 = null;
            }

            $qry1->free();

        }

//        $zipname = $folder.".zip";
//        $zip = new ZipArchive();
//        $zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);
//
//        // Create recursive directory iterator
//        $files = new RecursiveIteratorIterator(
//            new RecursiveDirectoryIterator($folder),
//            RecursiveIteratorIterator::LEAVES_ONLY
//        );
//
//        foreach ($files as $file)
//        {
//            // Skip directories (they would be added automatically)
//            if (!$file->isDir())
//            {
//                // Get real and relative path for current file
//                $filePath = $file->getRealPath();
//
//                // Add current file to archive
//                $zip->addFile($filePath, "images/".$file->getFilename());
//            }
//        }
//
//        // Zip archive will be created only after closing object
//        $zip->close();
//
//        $current = new SparkFile();
//        $current->setPath(CACHE_PATH);
//        $current->setFilename($zipname);
//
//        $response = new SparkHTTPResponse();
//        if ($current->exists()) {
//            $response->sendFile($current);
//        }


    }

    protected function processImpl() : void
    {

        //clear rendered state of startRender from SparkPage
        ob_end_clean();

        if (strcmp($this->type, self::TYPE_IMAGES) == 0) {
            $this->exportImages();
            exit;
        }

        $separator = ",";
        $enclosure = '"';
        $escape = "\\";
        $eol = PHP_EOL;

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment;filename=" . $this->type . "_" . self::FILENAME);
        $fp = fopen("php://output", "w");

        $process = null;

        if (strcmp($this->type, self::TYPE_GOOGLE) == 0) {

            $this->keys = array(
                "handleId",
                "fieldType",
                "name",
                "description",
                "productImageUrl",
                "collection",
                "sku",
                "ribbon",
                "price",
                "surcharge",
                "visible",
                "discountMode",
                "discountValue",
                "inventory",
                "weight",
                "productOptionName",
                "productOptionType",
                "productOptionDescription",
                "additionalInfoTitle",
                "Additional info description",
                "customTextField",
                "customTextCharLimit",
                "customTextMandatory"
            );
            $process = function(SellableItem $item) : array {
                return $this->processGoogle($item);
            };

        }
        else if (strcmp($this->type, self::TYPE_GOOGLE_MERCHANT) == 0) {
            $separator = "\t";
            $this->keys = array(
                "ID [id]",
                "Title [title]",
                "Description [description]",
                "Condition [condition]",
                "Link [link]",
                "Image link [image_link]",
                "Availability [availability]",
                "Price [price]"
            );
            $process = function(SellableItem $item) : array {
                return $this->processGoogleMerchant($item);
            };
        }
        else if (strcmp($this->type, self::TYPE_FACEBOOK) == 0)  {

            $this->keys = array("id", "content_id", "title", "description", "availability", "condition", "link", "image_link", "brand", "product_type", "price");

            $process = function(SellableItem $item) : array {
                return $this->processFacebook($item);
            };

        } else if (strcmp($this->type, self::TYPE_FULL) == 0) {

            $this->keys = array("id", "category", "name", "description", "price", "old_price", "images");

            $process = function (SellableItem $item): array {
                return $this->processFull($item);
            };

        }


        fputcsv($fp, $this->keys, $separator, $enclosure, $escape, $eol);

        $bean = new SellableProducts();

        $query = $bean->query("prodID");
        $query->select->group_by = " prodID ";
        $query->select->order_by = " update_date DESC ";
        if (isset($_GET["filter_catID"])) {
            $catID = (int)$_GET["filter_catID"];
            $query->select->where()->add("catID", $catID);
        }
        $total_rows = $query->exec();

        while ($result = $query->nextResult()) {

            $prodID = $result->get("prodID");

            $item = SellableItem::Load($prodID);

            fputcsv($fp, $process($item), $separator, $enclosure, $escape, $eol);
        }
        fclose($fp);


        exit;

    }

    protected function processFull(SellableItem $item) : array
    {
        $export_row = array();
        foreach ($this->keys as $idx => $value) {
            $export_row[$value] = "";
        }

        $export_row["id"] = $item->getProductID();
        $export_row["category"] = $item->getCategoryName();
        $export_row["name"] = $item->getTitle();
        $export_row["description"] = $item->getDescription();

        $imageID = array();
        foreach($item->galleryItems() as $idx=>$storageItem){
            if ($storageItem instanceof StorageItem) {
                $imageID[] = $storageItem->id;
            }
        }
        $export_row["images"] = implode("|", $imageID);

        $export_row["price"] = $item->getPriceInfo()->getSellPrice();
        $export_row["old_price"] = $item->getPriceInfo()->getOldPrice();
        return $export_row;

    }
    protected function processGoogleMerchant(SellableItem $item) : array
    {
        $export_row = array();
        foreach ($this->keys as $idx => $value) {
            $export_row[$value] = "";
        }

        $export_row["id"] = $item->getProductID();
        $export_row["title"] = $item->getTitle();
        $export_row["description"] = mb_substr(strip_tags($item->getDescription()), 0, 5000);
        $export_row["condition"] = "new";

        $link = LOCAL . "/products/details.php?prodID=" . $item->getProductID();
        $export_row["link"] = fullURL($link);

        $image_link = "";
        if ($item->getMainPhoto() instanceof StorageItem) {
            $image_link = $item->getMainPhoto()->hrefImage(640, -1);
            $image_link = fullURL($image_link);
        }
        $export_row["image_link"] = $image_link;

        $export_row["availability"] = "in_stock";
        $export_row["price"] = formatPrice($item->getPriceInfo()->getSellPrice() / DOUBLE_PRICE_RATE, "EUR", true);

        return $export_row;
    }
    protected function processGoogle(SellableItem $item) : array
    {

        $export_row = array();
        foreach ($this->keys as $idx => $value) {
            $export_row[$value] = "";
        }

        $export_row["handleId"] = $item->getProductID();
        $export_row["fieldType"] = "Product";
        $export_row["name"] = $item->getTitle();
        $export_row["description"] = mb_substr(strip_tags($item->getDescription()), 0, 8000);

        $image_link = "";
        if ($item->getMainPhoto() instanceof StorageItem) {
            $image_link = $item->getMainPhoto()->hrefImage(640, -1);
            $image_link = fullURL($image_link);
        }
        $export_row["productImageUrl"] = $image_link;

        $export_row["collection"] = $item->getCategoryName();
        $export_row["sku"] = $item->getProductID();
        $export_row["ribbon"] = "";
        if ($item->isPromotion()) {
            $export_row["ribbon"] = "Promo";
        }
        $export_row["price"] = $item->getPriceInfo()->getSellPrice();
        $export_row["surcharge"] = "";
        $export_row["visible"] = "TRUE";
        $export_row["discountMode"] = "AMOUNT";
        $export_row["discountValue"] = $item->getPriceInfo()->getDiscountAmount();
        $export_row["inventory"] = "InStock";
        $export_row["weight"] = "0.000";

        return $export_row;

    }

    protected function processFacebook(SellableItem $item) : array
    {


        $export_row = array();
        foreach ($this->keys as $idx => $value) {
            $export_row[$value] = "";
        }

        $export_row["id"] = $item->getProductID();
        $export_row["content_id"] = $item->getProductID();
        $export_row["title"] = $item->getTitle();
        $export_row["description"] = mb_substr(strip_tags($item->getDescription()), 0, 8000);
        $export_row["availability"] = "in stock";
        $export_row["condition"] = "new";

        $link = LOCAL . "/products/details.php?prodID=" . $item->getProductID();
        $export_row["link"] = fullURL($link);

        $image_link = "";
        if ($item->getMainPhoto() instanceof StorageItem) {
            $image_link = $item->getMainPhoto()->hrefImage(640, -1);
            $image_link = fullURL($image_link);
        }
        $export_row["image_link"] = $image_link;

        $export_row["brand"] = $item->getBrandName();
        $export_row["product_type"] = $item->getCategoryName();

        $export_row["price"] = $item->getPriceInfo()->getSellPrice();

        return $export_row;

    }

    /**
     * @return void
     * @throws Exception
     */
    protected function parseParams() : void
    {
        if (!$this->url->contains("type")) {
            throw new Exception("Target type not specified");
        }

        $type = $this->url->get("type")->value();

        if (!in_array($type, $this->supported_content)) throw new Exception("Type not supported");

        $this->type = $type;

    }

    public function getParameterNames() : array
    {
        $result = parent::getParameterNames();
        $result[] = "type";
        return $result;
    }
}
