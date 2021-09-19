<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("components/renderers/IHeadContents.php");
include_once("components/renderers/IPhotoRenderer.php");
include_once("storage/StorageItem.php");

include_once("utils/URLBuilder.php");
include_once("utils/DataParameter.php");

include_once("store/beans/ProductColorPhotosBean.php");
include_once("store/beans/ProductPhotosBean.php");

class ProductListItem extends DataIteratorItem implements IHeadContents, IPhotoRenderer
{

    //same product all inventory color data
    protected $colorSeries = array();

    /**
     * To render the main inventory photo
     * @var StorageItem
     */
    protected $photo;

    /**
     * To render the color chip
     * @var StorageItem
     */
    protected $chip;

    /**
     * Details page of this inventory
     * @var URLBuilder
     */
    protected $detailsURL;

    protected $width = 275;
    protected $height = 275;

    protected $chipSize = 36;

    protected $numColors = 0;

    /**
     * @var bool
     */
    protected $haveColorSeries = FALSE;

    public function __construct()
    {
        parent::__construct();

        $this->photo = new StorageItem();

        $this->chip = new StorageItem();

        $this->chip->className = "ProductColorPhotosBean";

        $this->detailsURL = new URLBuilder();
        $this->detailsURL->setScriptName(LOCAL . "/products/details.php");
        $this->detailsURL->add(new DataParameter("prodID"));
        $this->detailsURL->add(new DataParameter("piID"));

        $this->setAttribute("itemprop","itemListElement");
        $this->setAttribute("itemscope", "");
        $this->setAttribute("itemtype", "http://schema.org/Product");


    }

    public function getDetailsURL(): URLBuilder
    {
        return $this->detailsURL;
    }

    public function getPhoto(): StorageItem
    {
        return $this->photo;
    }

    public function getColorsCount() : int
    {
        return $this->numColors;
    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/ProductListItem.css";
        return $arr;
    }

    public function setPhotoSize(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getPhotoWidth(): int
    {
        return $this->width;
    }

    public function getPhotoHeight(): int
    {
        return $this->height;
    }

    public function setChipSize(int $chipSize)
    {
        $this->chipSize = $chipSize;
    }

    public function setData(array &$item)
    {
        parent::setData($item);
        $this->setAttribute("prodID", $this->data["prodID"]);
        $this->setAttribute("piID", $this->data["piID"]);

        if (isset($this->data["color_ids"]) && $this->data["color_ids"]) {

            $this->colorSeries["id"] = explode("|", $this->data["color_ids"]);
            $this->colorSeries["photo"] = explode("|", $this->data["color_photo_ids"]);
            $this->colorSeries["name"] =  explode("|", $this->data["color_names"]);
            $this->colorSeries["code"] = explode("|", $this->data["color_codes"]);
            $this->colorSeries["inventories"] = explode("|", $this->data["inventory_ids"]);

        }
        else {
            $this->colorSeries = array();
        }


        if (isset($item["ppID"]) && $item["ppID"] > 0) {

            $this->photo->id = (int)$item["ppID"];
            $this->photo->className = "ProductPhotosBean";//ProductPhotosBean::class;
        }
        else if (isset($item["pclrpID"]) && $item["pclrpID"] > 0) {

            $this->photo->id = (int)$item["pclrpID"];
            $this->photo->className = "ProductColorPhotosBean";//ProductColorPhotosBean::class;
        }


        $this->detailsURL->setData($item);

        $this->haveColorSeries = (count($this->colorSeries)>0);

        $this->numColors = 0;

        if (isset($this->colorSeries["id"])) {
            $this->numColors = count($this->colorSeries["id"]);
        }

        $this->clearAttribute("colorSeries");

        if ($this->numColors>0) {
            $this->setAttribute("colorSeries", "");
        }

//        print_r($item);
    }

    protected function renderImpl()
    {
        $this->renderMeta();

        echo "<div class='wrap'>";

            $this->renderPhoto();
            $this->renderColorChips();
            $this->renderDetails();

        echo "</div>"; //wrap

    }

    protected function renderMeta()
    {
        $title_alt = attributeValue($this->data["product_name"]);
        $details_url = $this->detailsURL->url();

        echo "<meta itemprop='position' content='".attributeValue($this->position)."'>";
        echo "<meta itemprop='url' content='".attributeValue(fullURL($details_url))."'>";
        echo "<meta itemprop='category' content='".attributeValue($this->data["category_name"])."'>";
        $description_content = $this->data["product_name"];

        if (isset($this->data["product_description"])) {
            $description_content = $this->data["product_description"];
        }
        else if (isset($this->data["long_description"])) {
            $description_content = $this->data["long_description"];
        }

        echo "<meta itemprop='description' content='".attributeValue($description_content)."'>";



    }

    protected function renderPhoto()
    {
        $title_alt = attributeValue($this->data["product_name"]);
        $details_url = $this->detailsURL->url();

        echo "<a class='photo' title='{$title_alt}' href='{$details_url}'>";
            $img_href = $this->photo->hrefImage($this->width, $this->height);

            echo "<img itemprop='image' src='$img_href' alt='$title_alt'>";

            if ($this->data["discount_percent"]>0) {
                echo "<div class='discount_label'> -".$this->data["discount_percent"]."%</div>";
            }
            else if ($this->isPromo()) {
                echo "<div class='discount_label'>Промо</div>";
            }

        echo "</a>";
    }

    public function isPromo()
    {
        return ((float)$this->data["price"] != (float)$this->data["sell_price"] && (float)$this->data["price"]>0);
    }

    protected function renderDetails()
    {

        echo "<a class='details' href='{$this->detailsURL->url()}'>";

            echo "<div itemprop='name' class='product_name'>" . $this->data["product_name"] . "</div>";

            //echo "<div class='stock_amount'><label>".tr("Наличност").": </label>".$this->item["stock_amount"]."</div>";

            $this->renderPrice();

        echo "</a>";

    }
    protected function renderPrice()
    {
        if ($this->data["sell_price"] > 0) {

            echo "<div class='price_info' itemprop='offers' itemscope itemtype='http://schema.org/Offer'>";

            echo "<div class='price old'>";
            if ($this->isPromo()) {
                echo sprintf("%1.2f", $this->data["price"]) . " " . tr("лв.");
            }
            else {
                echo "<BR>";
            }
            echo "</div>";

            echo "<meta itemprop='priceCurrency' content='" . DEFAULT_CURRENCY . "'>";
            echo "<div class='price sell'>";
            echo "<span itemprop='price'>" . sprintf("%1.2f", $this->data["sell_price"]) . "</span> ";
            echo tr("лв.");
            echo "</div>";

            //                if ($this->data["price_min"] != $this->data["sell_price"] || $this->data["price_max"] != $this->data["sell_price"]) {
            //                    echo "<div class='series_price'>" . sprintf("%1.2f", $this->data["price_min"]) . " " . tr("лв.") . " - " . sprintf("%1.2f", $this->data["price_max"]) . " " . tr("лв.") . "</div>";
            //                }

            echo "</div>";

        }
    }

    protected function renderColorChips()
    {

        if ($this->haveColorSeries < 1) return;

        $numColors = $this->numColors;

        if ($numColors < 1) return;

        echo "<div class='color_series'>";

            foreach ($this->colorSeries["id"] as $idx => $id) {

                $code ="#000000";
                if (isset($this->colorSeries["code"][$idx])) {
                    $code = $this->colorSeries["code"][$idx];
                }
                $name = $this->colorSeries["name"][$idx];

                $photoID = -1;
                if (isset($this->colorSeries["photo"][$idx]))$photoID = $this->colorSeries["photo"][$idx];
                $inventoryID = -1;
                if (isset($this->colorSeries["inventories"][$idx]))$inventoryID = $this->colorSeries["inventories"][$idx];

                $data["piID"] = $inventoryID;
                $this->detailsURL->setData($data);


                $backgroundImage = "";
                if ($photoID>0) {
                    $this->chip->id = $photoID;
                    $href = $this->chip->hrefThumb($this->chipSize);
                    $backgroundImage = "background-image:url($href);";
                }

                echo "<a href='{$this->detailsURL->url()}' class='chip' style='background-color:$code;min-width:{$this->chipSize}px;min-height:{$this->chipSize}px;{$backgroundImage}'  piID='$inventoryID' >";
                echo "</a>";
            }

        echo "</div>"; //color_series
    }

    public function renderSeparator($idx_curr, $items_total)
    {

    }

    public static function AttributesMeta(array $attributes, array $supported)
    {

        foreach ($attributes as $idx => $item) {
            if ($item["name"] && $item["value"]) {
                $attributeName = mb_strtolower($item["name"]);
                $attributeValue = mb_strtolower($item["value"]);
                foreach ($supported as $itemProp=>$matches) {
                    if (in_array($attributeName, $matches)) {
                        echo "<meta itemprop='$itemProp' content='".attributeValue($attributeValue)."'>";
                    }
                }
            }
        }

    }
}

?>
