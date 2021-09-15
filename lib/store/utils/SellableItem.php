<?php
include_once("store/utils/SellableDataParser.php");

class SellableItem implements JsonSerializable {


    protected $dataParser = null;


    protected $prodID = -1;

    /**
     * @var int landing/active sellable piID
     */
    protected $piID = -1;


    protected $title = "";
    protected $caption = "";
    protected $description = "";
    protected $keywords = "";

    protected $main_photo = null;

    //access by piID
    protected $rawData = array();
    protected $attributes = array();
    protected $prices = array();
    protected $sizes = array();
    protected $colors = array();

    //access by pclrID
    protected $color_codes = array();
    protected $color_names = array();
    protected $color_chips = array();
    protected $galleries = array();

    //protected $data = array();


    public function __construct(int $prodID, SellableDataParser $dataParser=null)
    {

        $this->prodID = $prodID;
        $this->dataParser = $dataParser;

        if (is_null($dataParser)) {
            $this->dataParser = new SellableDataParser();
        }


    }

    public function getData(int $piID, string $key)
    {
        $result = $this->rawData[$piID];

        return $result->get($key);
    }

    public function setRawResult(int $piID, RawResult $result)
    {
        $this->rawData[$piID] = $result;
    }

    public function finalize()
    {
        $this->dataParser->processMainPhoto($this);
    }

    public function setSizeValue(int $piID, string $size_value)
    {
        $this->sizes[$piID] = $size_value;
    }

    public function getSizeValue(int $piID) : string
    {
        return $this->sizes[$piID];
    }

    protected function pidsByColorID(int $pclrID) : array
    {
        $matching_piIDs = array();

        foreach ($this->colors as $piID=>$colorID) {
            if ($colorID == $pclrID) {
                $matching_piIDs[$piID] = 1;
                break;
            }
        }

        return array_keys($matching_piIDs);
    }

    public function getSizeValuesByColorID(int $pclrID) : array
    {

        $pids = $this->pidsByColorID($pclrID);

        $size_values = array();

        foreach ($pids as $idx=>$piID) {
            $size_values[$piID] = $this->getSizeValue($piID);
        }

        return $size_values;
    }

    public function getPriceInfosByColorID(int $pclrID) : array
    {
        $pids = $this->pidsByColorID($pclrID);

        $price_infos = array();
        foreach ($pids as $idx=>$piID) {
            $price_infos[$piID] = $this->getPriceInfo($piID);
        }
        return $price_infos;
    }



    public function setPriceInfo(int $piID, PriceInfo $info)
    {
        $this->prices[$piID] = $info;
    }
    public function getPriceInfo(int $piID) : PriceInfo
    {
        return $this->prices[$piID];
    }

    public function isPromotion(int $piID) : bool
    {
        $result = false;

        $priceInfo = $this->prices[$piID];
        if (!$priceInfo instanceof PriceInfo) return $result;
        if ($priceInfo->getOldPrice()!=$priceInfo->getSellPrice() && $priceInfo->getOldPrice()>0) {
            $result = true;
        }

        return $result;
    }

    public function setColorID(int $piID, int $pclrID)
    {
        $this->colors[$piID] = $pclrID;
    }

    public function getColorID(int $piID) : int
    {
        return $this->colors[$piID];
    }

    public function setDataParser(SellableDataParser $parser)
    {
        $this->dataParser = $parser;
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

    /**
     * @param int $piID Set the active/landing product inventory ID
     */
    public function setInventoryID(int $piID)
    {
        $this->piID = $piID;
    }

    public function getProductID(): int
    {
        return $this->prodID;
    }

    /**
     * @return int Get the active/landing product inventory ID
     */
    public function getActiveInventoryID() : int
    {
        return $this->piID;
    }

    public function addInventoryData(RawResult $result)
    {

        $this->dataParser->parse($this, $result);

    }

//    public function setData(int $piID, array $result)
//    {
//        $this->data[$piID] = $result;
//    }
//
//    public function getData(string $key) : ?string
//    {
//        if (isset($this->data[$this->getActiveInventoryID()][$key])) {
//            return $this->data[$this->getActiveInventoryID()][$key];
//        }
//        return null;
//    }

    public function getKeywords() : string
    {
        return  $this->keywords;
    }
    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
    public function getTitle() : string
    {
        return $this->title;
    }

    public function getCaption() : string
    {
        return $this->caption;
    }
    public function setCaption(string $caption)
    {
        $this->caption = $caption;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function setAttributes(int $piID, array $attributes)
    {
        $this->attributes[$piID] = $attributes;
    }

    public function getAttributes(int $piID) : array
    {
        return $this->attributes[$piID];
    }

    public function getAttributesAll() : array
    {
        return $this->attributes;
    }

    public function setColorName(int $pclrID, string $name)
    {
        $this->color_names[$pclrID] = $name;
    }
    public function getColorName(int $pclrID) : ?string
    {
        if (isset($this->color_names[$pclrID])) {
            return $this->color_names[$pclrID];
        }
        return null;
    }

    public function setColorCode(int $pclrID, string $code)
    {
        $this->color_codes[$pclrID] = $code;
    }

    public function getColorCode(int $pclrID) : ?string
    {
        if (isset($this->color_codes[$pclrID])) {
            return $this->color_codes[$pclrID];
        }
        return null;
    }

    public function galleries() : array
    {
        return array_keys($this->galleries);
    }

    public function haveGalleryItems(int $pclrID) : bool
    {

        return isset($this->galleries[$pclrID]);
    }

    public function galleryItems(int $pclrID) : array
    {
        return $this->galleries[$pclrID];
    }

    public function addGalleryItem(int $pclrID, StorageItem $sitem)
    {
        $this->galleries[$pclrID][] = $sitem;
    }


    //
    //
    public function setColorChip(int $pclrID, StorageItem $sitem=null)
    {
        $this->color_chips[$pclrID] = $sitem;
    }

    public function getColorChips() : array
    {
        return $this->color_chips;
    }

    public function getColorChip(int $pclrID) : ?StorageItem
    {
        return $this->color_chips[$pclrID];
    }


    //    public function setPrice(int $pclrID, string $size_value, int $piID, PriceInfo $pinfo) {
    //        $this->prices[$pclrID][$size_value][$piID] = $pinfo;
    //    }


    public function jsonSerialize() : array
    {
        return get_object_vars($this);
    }
}
?>