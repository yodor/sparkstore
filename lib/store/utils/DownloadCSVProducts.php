<?php
include_once("responders/RequestResponder.php");
include_once("components/Action.php");
include_once("storage/StorageItem.php");

include_once("store/beans/SellableProducts.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/ProductsSQL.php");
include_once("store/beans/ProductCategoriesBean.php");

class DownloadCSVProducts extends RequestResponder
{

    const COMMAND = "download_csv";
    const FILENAME = "catalog_products.csv";


    const TYPE_FACEBOOK = "facebook";
    const TYPE_GOOGLE = "google";
    protected array $supported_content = array();
    protected string $type = "";

    protected array $keys = array();

    public function __construct()
    {
        parent::__construct(self::COMMAND);

        $this->supported_content[] = self::TYPE_FACEBOOK;
        $this->supported_content[] = self::TYPE_GOOGLE;
    }

    public function createAction($title = FALSE, $href = FALSE, $check_code = NULL, $data_parameters = array())
    {
        $type = "";
        if (strcmp($title, self::TYPE_FACEBOOK) == 0) {
            $type = self::TYPE_FACEBOOK;
        } else if (strcmp($title, self::TYPE_GOOGLE) == 0) {
            $type = self::TYPE_GOOGLE;
        }

        $action = new Action(self::COMMAND."_".$type, "?cmd=" . self::COMMAND . "&type=" . $type);
        $action->setTooltipText("Download CSV - ".$type);
        return $action;
    }

    protected function processImpl()
    {

        //clear rendered state of startRender from SparkPage
        ob_end_clean();

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
            $process = function(SellableItem $item, string $category_name) : array {
                return $this->processGoogle($item, $category_name);
            };

        } else if (strcmp($this->type, self::TYPE_FACEBOOK) == 0)  {

            $this->keys = array("id", "content_id", "title", "description", "availability", "condition", "link", "image_link", "brand", "product_type", "price");

            $process = function(SellableItem $item, string $category_name) : array {
                return $this->processFacebook($item, $category_name);
            };
        }

        fputcsv($fp, $this->keys);

        $bean = new SellableProducts();

        $query = $bean->query("prodID");
        $query->select->group_by = " prodID ";
        $query->select->order_by = " update_date DESC ";

        $total_rows = $query->exec();

        $cats = new ProductCategoriesBean();

        while ($result = $query->nextResult()) {

            $prodID = $result->get("prodID");

            $item = SellableItem::Load($prodID);

            $category_name = $cats->getValue($item->getCategoryID(), "category_name");

            fputcsv($fp, $process($item, $category_name));
        }
        fclose($fp);
        exit;

    }

    protected function processGoogle(SellableItem $item, string $category_name)
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

        $export_row["collection"] = $category_name;
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

    protected function processFacebook(SellableItem $item, string $category_name)
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
        $export_row["product_type"] = $category_name;

        $export_row["price"] = $item->getPriceInfo()->getSellPrice();

        return $export_row;

    }

    protected function parseParams()
    {
        if (!$this->url->contains("type")) {
            throw new Exception("Target type not specified");
        }

        $type = $this->url->get("type")->value();

        if (!in_array($type, $this->supported_content)) throw new Exception("Type not supported");

        $this->type = $type;

    }
}
