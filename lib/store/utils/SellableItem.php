<?php
include_once("objects/SparkObject.php");
include_once("components/renderers/IPhotoRenderer.php");

include_once("store/utils/SellableDataParser.php");

class SellableItem extends SparkObject implements JsonSerializable, IPhotoRenderer
{

    protected $prodID = -1;
    protected $catID = -1;

    protected $title = "";
    protected $caption = "";
    protected $brand_name = "";
    protected $model = "";

    protected $description = "";
    protected $keywords = "";

    protected $main_photo = NULL;

    protected $width = -1;
    protected $height = -1;

    //attr.name=>value
    protected $attributes = array();

    //array of VariantItems
    protected $variants = array();

    protected $priceInfo = null;

    protected $gallery = array();

    protected $stock_amount = 0;

    protected $data = array();

    protected static $defaultDataParser=null;

    public static function SetDefaultDataParser(SellableDataParser $parser): void
    {
        SellableItem::$defaultDataParser = $parser;
    }

    public static function GetDefaultDataParser() : SellableDataParser
    {
        if (is_null(SellableItem::$defaultDataParser)){
            SellableItem::$defaultDataParser = new SellableDataParser();
        }
        return SellableItem::$defaultDataParser;
    }

    public static function Load(int $prodID) : SellableItem
    {
        $bean = new SellableProducts();

        $qry = $bean->queryFull();
        $qry->setKey("prodID");

        $qry->select->where()->add("prodID", $prodID);
        $qry->select->group_by = "prodID";

        $num = $qry->exec();
        if ($num < 1) throw new Exception("Product does not exist or is not accessible right now");

        $sellable = new SellableItem();
        SellableItem::GetDefaultDataParser()->parse($sellable, $qry->nextResult());
        return $sellable;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function setData(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }

    public function getData(string $key) : string
    {
        return $this->data[$key];
    }

    public function setBrandName(string $brand_name): void
    {
        $this->brand_name = $brand_name;
    }
    public function getBrandName() : string
    {
        return $this->brand_name;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getModel() : string
    {
        return $this->model;
    }

    public function setCategoryID(int $catID): void
    {
        $this->catID = $catID;
    }

    public function getCategoryID(): int
    {
        return $this->catID;
    }
    public function getPriceInfo() : PriceInfo
    {

        return $this->priceInfo;
    }

    public function setPriceInfo(PriceInfo $info): void
    {
        $this->priceInfo = $info;
    }

    public function setStockAmount(int $amount): void
    {
        $this->stock_amount = $amount;
    }

    public function getStockAmount() : int
    {
        return $this->stock_amount;
    }

    public function isPromotion() : bool
    {
        $result = false;

        $priceInfo = $this->priceInfo;
        if (!$priceInfo instanceof PriceInfo) return $result;
        if ($priceInfo->getOldPrice()!=$priceInfo->getSellPrice() && $priceInfo->getOldPrice()>0) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return StorageItem
     */
    public function getMainPhoto() : ?StorageItem
    {
        return $this->main_photo;
    }
    public function setMainPhoto(StorageItem $sitem)
    {
        $this->main_photo = $sitem;
    }

    public function setProductID(int $prodID): void
    {
        $this->prodID = $prodID;
    }

    public function getProductID(): int
    {
        return $this->prodID;
    }

    public function getKeywords() : string
    {
        return  $this->keywords;
    }
    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function setAttribute(string $name, string $value)
    {
        $this->attributes[$name] = $value;
    }

    public function getAttributes() : array
    {
        return $this->attributes;
    }

    public function galleryItems() : array
    {
        return $this->gallery;
    }

    public function addGalleryItem(StorageItem $sitem): void
    {
        $this->gallery[] = $sitem;
    }

    public function haveGalleryItems() : bool
    {
        return (count($this->gallery)>0);
    }

    public function setVariant(VariantItem $vitem): void
    {
        $this->variants[$vitem->getName()] = $vitem;
    }

    public function haveVariant(string $name) : bool
    {
        return array_key_exists($name, $this->variants);
    }

    public function getVariant(string $name) : VariantItem
    {
        if (!$this->haveVariant($name)) throw new Exception("Variant name '$name' not found");
        return $this->variants[$name];
    }

    public function getVariantNames() : array
    {
        return array_keys($this->variants);
    }

    public function variantsCount() : int
    {
        return count(array_keys($this->variants));
    }

    /**
     * Set the preferred size of the main photo (used from the JS code image gallery)
     * @param int $width
     * @param int $height
     */
    public function setPhotoSize(int $width, int $height): void
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Get the main photo preferred width
     * @return int
     */
    public function getPhotoWidth(): int
    {
        return $this->width;
    }

    /**
     * Get the main photo preferred height
     * @return int
     */
    public function getPhotoHeight(): int
    {
        return $this->height;
    }

    public function jsonSerialize() : array
    {
        return get_object_vars($this);
    }



}
?>