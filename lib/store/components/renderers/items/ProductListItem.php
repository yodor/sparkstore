<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("components/renderers/IHeadContents.php");
include_once("components/renderers/IPhotoRenderer.php");
include_once("storage/StorageItem.php");

include_once("utils/url/URL.php");
include_once("utils/url/DataParameter.php");

include_once("store/beans/ProductPhotosBean.php");

class ProductListItem extends DataIteratorItem implements IHeadContents, IPhotoRenderer
{


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
     * @var URL
     */
    protected $detailsURL;

    protected $width = 275;
    protected $height = 275;

    public function __construct()
    {
        parent::__construct(false);

        $this->photo = new StorageItem();

        $this->detailsURL = new URL();
        $this->detailsURL->setScriptName(LOCAL . "/products/details.php");
        $this->detailsURL->add(new DataParameter("prodID"));

        $this->setAttribute("itemprop","itemListElement");
        $this->setAttribute("itemscope", "");
        $this->setAttribute("itemtype", "http://schema.org/ListItem");

        //chainloading is disabled set component class
        $this->setComponentClass("ProductListItem");
    }

    public function getDetailsURL(): URL
    {
        return $this->detailsURL;
    }

    public function getPhoto(): StorageItem
    {
        return $this->photo;
    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/ProductListItem.css";
        return $arr;
    }

    public function setPhotoSize(int $width, int $height): void
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

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->setAttribute("prodID", $this->data["prodID"]);

        if (isset($data["ppID"]) && $data["ppID"] > 0) {

            $this->photo->id = (int)$data["ppID"];
            $this->photo->className = "ProductPhotosBean";//ProductPhotosBean::class;
        }

        $this->detailsURL->setData($data);

    }

    protected function renderImpl()
    {
        $title_alt = attributeValue($this->data["product_name"]);
        $details_url = attributeValue($this->getDetailsURL()->fullURL()->toString());
        $img_href = $this->photo->hrefImage($this->width, $this->height);

        //meta for ListItem
        echo "<meta itemprop='position' content='{$this->position}'>";
        echo "<meta itemprop='name' content='{$title_alt}'>";
        echo "<meta itemprop='url' content='{$details_url}'>";
        echo "<meta itemprop='image' content='{$img_href}'>";

        echo "<div class='wrap' itemscope itemtype='http://schema.org/Product'>";

            //meta for product
            $this->renderMeta();

            $this->renderPhoto();

            $this->renderDetails();

        echo "</div>"; //wrap

    }

    protected function renderMeta()
    {
        $title_alt = attributeValue($this->data["product_name"]);
        $details_url = $this->getDetailsURL()->toString();

        echo "<meta itemprop='url' content='".attributeValue(fullURL($details_url))."'>";
        echo "<meta itemprop='category' content='".attributeValue($this->data["category_name"])."'>";
        $description_content = $this->data["product_name"];

        echo "<meta itemprop='description' content='".attributeValue($description_content)."'>";
    }

    protected function renderPhoto()
    {
        $title_alt = attributeValue($this->data["product_name"]);
        $details_url = $this->getDetailsURL()->toString();

        echo "<a class='photo' title='{$title_alt}' href='{$details_url}'>";
            $img_href = $this->photo->hrefImage($this->width, $this->height);

            echo "<img loading='lazy' itemprop='image' src='$img_href' alt='$title_alt'>";

            if ($this->data["discount_percent"]>0) {
                echo "<div class='discount_label'> -".$this->data["discount_percent"]."%</div>";
            }
            else if ($this->isPromo()) {
                echo "<div class='discount_label'>Промо</div>";
            }
            if ($this->data["stock_amount"]<1) {
                echo "<div class='discount_label'>Изчерпан</div>";
                echo "<div class='blend'></div>";
            }
        echo "</a>";
    }

    public function isPromo()
    {
        return ((float)$this->data["price"] != (float)$this->data["sell_price"] && (float)$this->data["price"]>0);
    }

    protected function renderDetails()
    {

        echo "<a class='details' href='{$this->getDetailsURL()->toString()}'>";

            echo "<h3 itemprop='name' class='product_name'>" . $this->data["product_name"] . "</h3>";

            $this->renderBrand();

            $this->renderPrice();

        echo "</a>";

    }

    protected function renderBrand()
    {
//        $brand_name = $this->data["brand_name"];
//
//        echo "<div class='brand_name'>";
//        if ($brand_name) {
//            echo "<label>".tr("Марка") . ": $brand_name</label>";
//        }
//        else {
//            echo "<BR>";
//        }
//        echo "</div>";
    }

    protected function renderPrice()
    {
        if ($this->data["sell_price"] < 1) return;

        echo "<div class='price_info' itemprop='offers' itemscope itemtype='http://schema.org/Offer'>";

        $priceValidUntil = date("Y-m-d", strtotime("+1 year"));
        echo "<meta itemprop='priceValidUntil' content='$priceValidUntil'>";

        if ($this->data["stock_amount"]>0) {
            echo "<link itemprop='availability' href='https://schema.org/InStock'>";
        }
        else {
            echo "<link itemprop='availability' href='https://schema.org/OutOfStock'>";
        }

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

        echo "</div>";


    }

    public static function AttributesMeta(array $attributes, array $supported)
    {

        foreach ($attributes as $name => $value) {
            if ($name && $value) {
                $attributeName = mb_strtolower($name);
                $attributeValue = mb_strtolower($value);
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
