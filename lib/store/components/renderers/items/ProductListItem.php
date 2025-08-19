<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("components/renderers/IHeadContents.php");
include_once("components/renderers/IPhotoRenderer.php");
include_once("storage/StorageItem.php");

include_once("store/utils/url/ProductURL.php");
include_once("utils/url/DataParameter.php");

include_once("store/beans/ProductPhotosBean.php");
class PriceLabel extends Container {

    protected ?Link $availabilityLink = null;
    protected ?Meta $currencyMeta = null;

    protected ?Container $priceOld = null;
    protected ?Container $priceSell = null;

    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("price_info");

        $this->setAttribute("itemscope");
        $this->setAttribute("itemprop", "offers");
        $this->setAttribute("itemtype", "http://schema.org/Offer");

        $priceValidUntil = date("Y-m-d", strtotime("+1 year"));
        $metaValidUntil = new Meta();
        $metaValidUntil->setAttribute("itemprop", "priceValidUntil");
        $metaValidUntil->setContent($priceValidUntil);
        $this->items()->append($metaValidUntil);

        $this->availabilityLink = new Link();
        $this->availabilityLink->removeAttribute("rel");
        $this->availabilityLink->setAttribute("itemprop", "availability");
        $this->items()->append($this->availabilityLink);

        $this->currencyMeta = new Meta();
        $this->currencyMeta->setAttribute("itemprop", "priceCurrency");
        $this->currencyMeta->setContent(DEFAULT_CURRENCY);
        $this->items()->append($this->currencyMeta);

        $this->priceOld = new Container(false);
        $this->priceOld->setComponentClass("price");
        $this->priceOld->addClassName("old");
        $this->priceOld->setContents("<BR>");
        $this->items()->append($this->priceOld);

        $this->priceSell = new Container(false);
        $this->priceSell->setComponentClass("price");
        $this->priceSell->addClassName("sell");
        $this->priceSell->setContents("<BR>");
        $this->items()->append($this->priceSell);

    }

    public function availability() : Link
    {
        return $this->availabilityLink;
    }

    public function currency() : Meta
    {
        return $this->currencyMeta;
    }

    public function priceOld() : Container
    {
        return $this->priceOld;
    }
    public function priceSell() : Container
    {
        return $this->priceSell;
    }
}
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

    protected bool $product_linked_data_enabled = true;

    protected PriceLabel $priceLabel;

    public function __construct()
    {
        parent::__construct();

        $this->photo = new StorageItem();

        $this->detailsURL = new ProductURL();

        $this->setAttribute("itemprop","itemListElement");
        $this->setAttribute("itemscope", "");
        $this->setAttribute("itemtype", "http://schema.org/ListItem");

        //chainloading is disabled set component class
        $this->setComponentClass("ProductListItem");
        $this->setTagName("article");

        $this->initPriceLabel();

    }

    protected function initPriceLabel() : void
    {
        $this->priceLabel = new PriceLabel();
        if ($this->product_linked_data_enabled && LINKED_DATA_ENABLED) {

        }
        else {
            $this->priceLabel->removeAttribute("itemscope");
            $this->priceLabel->removeAttribute("itemprop");
            $this->priceLabel->removeAttribute("itemtype");
            $this->priceLabel->availability()->setRenderEnabled(false);
            $this->priceLabel->currency()->setRenderEnabled(false);
        }

    }

    public function setProductLinkedDataEnabled(bool $mode) : void
    {
        $this->product_linked_data_enabled = $mode;
        $this->initPriceLabel();
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

    protected function renderImpl(): void
    {
//        $title_alt = attributeValue($this->data["product_name"]);
//        $details_url = attributeValue($this->getDetailsURL()->fullURL()->toString());
//        $img_href = $this->photo->hrefImage($this->width, $this->height);

        //meta for ListItem
        echo "<meta itemprop='position' content='{$this->position}'>";

        $closure = function() {
            $this->renderMeta();
            $this->renderPhoto();
            $this->renderDetails();
        };

        $wrap = new ClosureComponent($closure,true, false);
        $wrap->setComponentClass("wrap");

        if ($this->product_linked_data_enabled && LINKED_DATA_ENABLED) {
            $wrap->setAttribute("itemscope", "");
            $wrap->setAttribute("itemtype", "http://schema.org/Product");
        }

        $wrap->render();


    }

    protected function renderMeta()
    {
        $details_url = $this->getDetailsURL()->toString();

        if ($this->product_linked_data_enabled && LINKED_DATA_ENABLED) {
            echo "<meta itemprop='sku' content='" .$this->data["prodID"]."'>";
            echo "<meta itemprop='url' content='" . attributeValue(fullURL($details_url)) . "'>";
            echo "<meta itemprop='category' content='" . attributeValue($this->data["category_name"]) . "'>";
            $description_content = $this->data["product_name"];

            echo "<meta itemprop='description' content='" . attributeValue($description_content) . "'>";
        }
    }

    protected function renderPhoto()
    {
        $title_alt = attributeValue($this->data["product_name"]);
        $details_url = $this->getDetailsURL()->toString();

        echo "<a class='photo' title='{$title_alt}' href='{$details_url}'>";

            $this->photo->setName($title_alt);
            $img_href = $this->photo->hrefImage($this->width, $this->height);

            $lazy = "";
            if ($this->position>3) $lazy="loading='lazy'";

            echo "<img $lazy itemprop='image' src='$img_href' alt='$title_alt'>";


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

        if ($this->data["stock_amount"]>0) {
            $this->priceLabel->availability()->setHref("https://schema.org/InStock");
        }
        else {
            $this->priceLabel->availability()->setHref("https://schema.org/OutOfStock");
        }

        echo "<div class='price_label'>";

        if (DOUBLE_PRICE_ENABLED) {
            $this->priceLabel->addClassName("left");
            $this->priceLabel->currency()->setContent("EUR");
            $priceOld = "<BR>";
            if ($this->isPromo()) {
                $priceOld = formatPrice( $this->data["price"] / DOUBLE_PRICE_RATE,"&euro;", true);
            }
            $this->priceLabel->priceOld()->setContents($priceOld);

            $priceSell = formatPrice($this->data["sell_price"] / DOUBLE_PRICE_RATE, "", true);
            $priceSell = "<span class='currency'>&euro;&nbsp;</span><span itemprop='price'>$priceSell</span>";
            $this->priceLabel->priceSell()->setContents($priceSell);

            $this->priceLabel->render();
        }

        $this->priceLabel->removeClassName("left");
        $this->priceLabel->currency()->setContent(DEFAULT_CURRENCY);

        $priceOld = "<BR>";
        if ($this->isPromo()) {
            $priceOld = formatPrice($this->data["price"],"лв", false);
        }
        $this->priceLabel->priceOld()->setContents($priceOld);

        $priceSell = formatPrice($this->data["sell_price"], "", false);
        $priceSell = "<span itemprop='price'>$priceSell</span><span class='currency'>&nbsp;лв.</span>";
        $this->priceLabel->priceSell()->setContents($priceSell);

        $this->priceLabel->render();

        echo "</div>";


    }


}

?>
