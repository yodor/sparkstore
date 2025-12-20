<?php
include_once("objects/SparkObject.php");
include_once("store/utils/SellableDataParser.php");

class SellableItem extends SparkObject
{

    protected int $prodID = -1;
    protected int $catID = -1;

    protected string $category_name = "";
    protected array $category_path = array();

    protected string $class_name = "";

    protected string $title = "";
    protected string $caption = "";
    protected string $brand_name = "";
    protected string $model = "";

    protected string $description = "";
    protected string $seoDescription = "";
    protected string $keywords = "";

    protected ?StorageItem $main_photo = NULL;

    protected int $width = -1;
    protected int $height = -1;

    //attr.name=>value
    protected array $attributes = array();

    //array of VariantItems
    protected array $variants = array();

    protected PriceInfo $priceInfo;

    protected array $gallery = array();

    protected int $stock_amount = 0;

    protected array $data = array();

    protected static $defaultDataParser = null;

    public static function SetDefaultDataParser(SellableDataParser $parser): void
    {
        SellableItem::$defaultDataParser = $parser;
    }

    public static function GetDefaultDataParser(): SellableDataParser
    {
        if (is_null(SellableItem::$defaultDataParser)) {
            SellableItem::$defaultDataParser = new SellableDataParser();
        }
        return SellableItem::$defaultDataParser;
    }

    public static function Load(int $prodID): SellableItem
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
        $this->priceInfo = new PriceInfo(0, 0, 0);
    }

    public function setData(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }

    public function getData(string $key): string
    {
        return $this->data[$key];
    }

    public function setBrandName(string $brand_name): void
    {
        $this->brand_name = $brand_name;
    }

    public function getBrandName(): string
    {
        return $this->brand_name;
    }

    public function setCategoryName(string $category_name): void
    {
        $this->category_name = $category_name;
    }

    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    public function getClassName(): string
    {
        return $this->class_name;
    }

    public function setClassName(string $class_name): void
    {
        $this->class_name = $class_name;
    }

    public function isClass(string $class_name): bool
    {
        return (strcmp($this->class_name, $class_name) === 0);
    }

    public function setCategoryPath(array $path_array) : void
    {
        $this->category_path = $path_array;
    }
    public function getCategoryPath(): array
    {
        return $this->category_path;
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

        if ($this->priceInfo->getOldPrice()!=$this->priceInfo->getSellPrice() && $this->priceInfo->getOldPrice()>0) {
            return true;
        }

        return false;
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
    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    public function getSeoDescription() : string
    {
        return $this->seoDescription;
    }
    public function setSeoDescription(string $seoDescription) : void
    {
        $this->seoDescription = $seoDescription;
    }

    public function setAttribute(string $name, string $value)
    {
        $this->attributes[$name] = $value;
    }

    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * Check presence and non-empty value of attribute with key '$name'
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name) : bool
    {
        return isset($this->attributes[$name]) && $this->attributes[$name];
    }

    public function getAttribute(string $name) : ?string
    {
        return $this->attributes[$name] ?? null;
    }
    /**
     * Product photo gallery
     * @return array Array of StorageItems
     */
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



}
?>
