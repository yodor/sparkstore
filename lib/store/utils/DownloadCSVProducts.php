<?php
include_once("responders/RequestResponder.php");
include_once("components/Action.php");
include_once("storage/StorageItem.php");

include_once("store/beans/SellableProducts.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/ProductsSQL.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("storage/SparkHTTPResponse.php");

include_once("store/utils/url/ProductURL.php");
include_once("store/utils/url/CategoryURL.php");

abstract class ProductExporter {
    protected string $typeName = "";

    public function __construct()
    {

    }

    public abstract function getToolTipText() : string;

    public abstract function process() : void;

    public function getTypeName() : string
    {
        return $this->typeName;
    }

}

class ImagesExporter extends ProductExporter {

    public function __construct()
    {
        parent::__construct();
        $this->typeName = "images";
    }

    public function getToolTipText() : string
    {
        return "Download full products - images part";
    }

    public function process() : void
    {

        //ini_set('max_execution_time', 300);

        $folder = Spark::Get(Config::CACHE_PATH)."/catalog-images-".time();
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
}

abstract class CSVProductExporter extends ProductExporter
{
    protected string $FILENAME = "catalog_products.csv";

    protected array $keys = array();
    protected array $values = array();
    protected string $SEPARATOR = ",";
    protected string $ENCLOSURE = '"';
    protected string $ESCAPE = "\\";
    protected string $EOL = PHP_EOL;
    protected mixed $fp = null;

    public function __construct()
    {
        parent::__construct();
        $this->keys = array();
        $this->values = array();
        $this->createKeys();
    }

    protected function defaultSelect() : SQLSelect
    {
        $bean = new SellableProducts();

        $query = $bean->query("prodID");
        $query->select->group_by = " prodID ";

        if (isset($_GET["filter_catID"])) {
            $catID = (int)$_GET["filter_catID"];
            $query->select->where()->add("catID", $catID);
        }
        return $query->select;
    }
    public function process() : void
    {

        $select = null;

        if (Session::Contains("ProductListSelect")) {
            $select = Session::get("ProductListSelect");
            @$select = unserialize($select);
        }

        if (!($select instanceof SQLSelect)) {
            Debug::ErrorLog("Using default ProductListSelect");
            $select = $this->defaultSelect();
        }
        else {
            Debug::ErrorLog("Using Deserialized ProductListSelect");
        }

        $select->fields()->set("prodID");


        $query = new SQLQuery($select);

        $this->processQuery($query);

        $total_rows = $query->exec();

        $this->writeHeader();
        while ($result = $query->nextResult()) {
            try {
                $this->writeItem(SellableItem::Load($result->get("prodID")));
            }
            catch (Exception $e) {
                //
            }
        }
    }

    protected function processQuery(SQLQuery $query) : void
    {
        $query->select->order_by = " update_date DESC ";
    }

    public function getToolTipText() : string
    {
        return "Download CSV - " . $this->typeName;
    }

    public function __destruct()
    {
        if ($this->fp) {
            fclose($this->fp);
        }
    }

    /**
     * Cleanup the value associative array and prepare
     * @return void
     */
    protected function resetValues() : void
    {
        $this->values = array();
        foreach ($this->keys as $idx => $keyName) {
            $this->values[$keyName] = "";
        }
    }

    public function writeHeader() : void
    {
        $outputFilename = $this->typeName . "_" . $this->FILENAME;
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment;filename=".$outputFilename);
        $this->fp = fopen("php://output", "w");
        fputcsv($this->fp, $this->keys, $this->SEPARATOR, $this->ENCLOSURE, $this->ESCAPE);
    }

    /**
     * Output using fputcsv the values array
     * @param SellableItem $item
     * @return void
     */
    public function writeItem(SellableItem $item) : void
    {
        $this->resetValues();
        $this->processItem($item);
        fputcsv($this->fp, $this->values, $this->SEPARATOR, $this->ENCLOSURE, $this->ESCAPE);
    }

    /**
     * Setup the $keys associative array
     * @return void
     */
    protected abstract function createKeys() : void;

    /**
     * Fill values using data from SellableItem
     * @param SellableItem $item
     * @return void
     */
    protected abstract function processItem(SellableItem $item) : void;

}

class FacebookCSVExporter extends CSVProductExporter
{
    public function __construct()
    {
        parent::__construct();
        $this->typeName = "facebook";
    }

    protected function createKeys(): void
    {
        $this->keys = array("id", "content_id", "title", "description", "availability", "condition", "link", "image_link", "brand", "product_type", "price");

    }

    protected function processItem(SellableItem $item): void
    {
        $this->values["id"] = $item->getProductID();
        $this->values["content_id"] = $item->getProductID();
        $this->values["title"] = $item->getTitle();
        $this->values["description"] = mb_substr(strip_tags($item->getDescription()), 0, 8000);
        $this->values["availability"] = "in stock";
        $this->values["condition"] = "new";

        $productURL = new ProductURL();
        $productURL->setProductID($item->getProductID());
        $this->values["link"] = $productURL->fullURL();

        $image_link = "";
        if ($item->getMainPhoto() instanceof StorageItem) {
            $image_link = $item->getMainPhoto()->hrefImage(640, -1);
            $image_link = new URL($image_link)->fullURL();
        }
        $this->values["image_link"] = $image_link;

        $this->values["brand"] = $item->getBrandName();
        $this->values["product_type"] = $item->getCategoryName();

        $this->values["price"] = $item->getPriceInfo()->getSellPrice();
    }
}

class GoogleCSVExporter extends CSVProductExporter
{
    public function __construct()
    {
        parent::__construct();
        $this->typeName = "google";
    }

    protected function createKeys(): void
    {
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
    }

    protected function processItem(SellableItem $item): void
    {

        $this->values["handleId"] = $item->getProductID();
        $this->values["fieldType"] = "Product";
        $this->values["name"] = $item->getTitle();
        $this->values["description"] = mb_substr(strip_tags($item->getDescription()), 0, 8000);

        $image_link = "";
        if ($item->getMainPhoto() instanceof StorageItem) {
            $image_link = $item->getMainPhoto()->hrefImage(640, -1);
            $image_link = new URL($image_link)->fullURL();
        }
        $this->values["productImageUrl"] = $image_link;

        $this->values["collection"] = $item->getCategoryName();
        $this->values["sku"] = $item->getProductID();
        $this->values["ribbon"] = "";
        if ($item->isPromotion()) {
            $this->values["ribbon"] = "Promo";
        }
        $this->values["price"] = $item->getPriceInfo()->getSellPrice();
        $this->values["surcharge"] = "";
        $this->values["visible"] = "TRUE";
        $this->values["discountMode"] = "AMOUNT";
        $this->values["discountValue"] = $item->getPriceInfo()->getDiscountAmount();
        $this->values["inventory"] = "InStock";
        $this->values["weight"] = "0.000";
    }
}

class GoogleMerchantCSVExporter extends CSVProductExporter
{

    public function __construct()
    {
        parent::__construct();
        $this->typeName = "google_merchant";
        $this->SEPARATOR = "\t";
    }

    protected function createKeys(): void
    {

        $this->keys = array(
            "id",
            "title",
            "description",
            "condition",
            "link",
            "image_link",
            "availability",
            "price"
        );
    }

    protected function processItem(SellableItem $item): void
    {
        $this->values["id"] = $item->getProductID();
        $this->values["title"] = $item->getTitle();
        $this->values["description"] = mb_substr(strip_tags($item->getDescription()), 0, 5000);
        $this->values["condition"] = "new";

        $productURL = new ProductURL();
        $productURL->setProductID($item->getProductID());
        $this->values["link"] = $productURL->fullURL();

        $image_link = "";
        if ($item->getMainPhoto() instanceof StorageItem) {
            $image_link = $item->getMainPhoto()->hrefImage(640, -1);
            $image_link = new URL($image_link)->fullURL();
        }
        $this->values["image_link"] = $image_link;

        $this->values["availability"] = "in_stock";
        $this->values["price"] = formatPrice($item->getPriceInfo()->getSellPrice());

    }
}

class FullCSVExporter extends CSVProductExporter
{

    public function __construct()
    {
        parent::__construct();
        $this->typeName = "full";
    }

    public function getToolTipText(): string
    {
        return "Download CSV full products - data part";
    }

    protected function createKeys(): void
    {
        $this->keys = array("id", "category", "name", "seo_description", "description", "price", "old_price", "images");
    }

    protected function processItem(SellableItem $item): void
    {
        $this->values["id"] = $item->getProductID();
        $this->values["category"] = $item->getCategoryName();
        $this->values["name"] = $item->getTitle();
        $this->values["seo_description"] = $item->getSeoDescription();
        $this->values["description"] = $item->getDescription();

        $imageID = array();
        foreach($item->galleryItems() as $idx=>$storageItem){
            if ($storageItem instanceof StorageItem) {
                $imageID[] = $storageItem->id;
            }
        }
        $this->values["images"] = implode("|", $imageID);

        $this->values["price"] = $item->getPriceInfo()->getSellPrice();
        $this->values["old_price"] = $item->getPriceInfo()->getOldPrice();
    }
}

class UpdateCSVExporter extends CSVProductExporter
{
    public function __construct()
    {
        parent::__construct();
        $this->typeName = "export_update";
    }

    public function getToolTipText(): string
    {
        return "Download products data for external edit";
    }

    protected function createKeys(): void
    {
        $this->keys = array("prodID", "product_name", "product_description", "seo_description");
    }

    protected function processItem(SellableItem $item): void
    {
        $this->values["prodID"] = $item->getProductID();
        $this->values["product_name"] =  $item->getTitle();
        $this->values["product_description"] = $item->getDescription();
        $this->values["seo_description"] = $item->getSeoDescription();
    }

    protected function processQuery(SQLQuery $query): void
    {
        $query->select->order_by = " prodID DESC ";
    }
}

class DownloadCSVProducts extends RequestResponder
{

    protected array $supported_content = array();
    protected string $type = "";
    protected array $processors = array();

    public function __construct()
    {
        parent::__construct();

        $this->addProcessor(new FacebookCSVExporter());
        $this->addProcessor(new GoogleCSVExporter());
        $this->addProcessor(new GoogleMerchantCSVExporter());
        $this->addProcessor(new FullCSVExporter());
        $this->addProcessor(new ImagesExporter());
        $this->addProcessor(new UpdateCSVExporter());
    }

    public function addProcessor(ProductExporter $processor) : void
    {
        $this->supported_content[] = $processor->getTypeName();
        $this->processors[$processor->getTypeName()] = $processor;
    }

    public function getProcessor(string $typeName) : CSVProductExporter
    {
        return $this->processors[$typeName];
    }

    public function getProcessorTypes() : array
    {
        return array_keys($this->processors);
    }

    public function createAction(string $title = ""): ?Action
    {
        if (!isset($this->processors[$title])) {
            throw new Exception("Unknown processor type $title");
        }
        $type = $title;
        $processor = $this->processors[$type];
        $tooltip = $processor->getToolTipText();

        $action = parent::createAction($title);
        $action->getURL()->add(new URLParameter("type", $type));
        $action->setTooltip($tooltip);

        return $action;
    }

    protected function processImpl() : void
    {

        //clear rendered state of startRender from SparkPage
        ob_end_clean();

        $processor = null;

        if (array_key_exists($this->type, $this->processors)) {
            $processor = $this->processors[$this->type];
        }

        if (!($processor instanceof ProductExporter)) throw new Exception("Export processor was not created");

        $processor->process();

        exit;

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