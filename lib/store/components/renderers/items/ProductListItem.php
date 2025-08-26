<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("components/renderers/IHeadContents.php");
include_once("components/renderers/IPhotoRenderer.php");
include_once("storage/StorageItem.php");

include_once("store/utils/url/ProductURL.php");
include_once("utils/url/DataParameter.php");

include_once("store/beans/ProductPhotosBean.php");
class ProductPhoto extends Action
{
    protected Image $image;
    protected Component $discountLabel;
    protected Component $blend;
    protected ProductListItem $item;

    public function __construct(ProductListItem $item)
    {
        parent::__construct();
        $this->item = $item;

        $this->setComponentClass("photo");

        $this->setAttribute("itemprop", "url");

        $this->image = new Image();
        $this->image->setAttribute("loading", "lazy");
        $this->image->setAttribute("itemprop", "image");
        $this->image->setStorageItem(new StorageItem());

        $this->image->setPhotoSize(275,275);

        $this->items()->append($this->image);

        $this->discountLabel = new Component(false);
        $this->discountLabel->setComponentClass("discount_label");
        $this->discountLabel->setRenderEnabled(false);
        $this->items()->append($this->discountLabel);

        $this->blend = new Component(false);
        $this->blend->setComponentClass("blend");
        $this->blend->setRenderEnabled(false);
        $this->items()->append($this->blend);
    }

    public function getDiscountLabel() : Component
    {
        return $this->discountLabel;
    }

    public function getBlend() : Component
    {
        return $this->blend;
    }

    public function getImage() : Image
    {
        return $this->image;
    }

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->image->getStorageItem()->setData($data);

        $this->setAttribute("title", $this->data["product_name"]);
        $this->image->setTitle($this->data["product_name"]);

        $this->discountLabel->setContents("");
        $this->discountLabel->setRenderEnabled(false);
        $this->blend->setRenderEnabled(false);

        if ($this->data["discount_percent"]>0) {
            $this->discountLabel->setContents(" -".$this->data["discount_percent"]."%");
        }
        else if ($this->item->isPromo()) {
            $this->discountLabel->setContents("Промо");
        }
        if ($this->data["stock_amount"]<1) {
            $this->discountLabel->setContents("Изчерпан");
            $this->blend->setRenderEnabled(true);
        }
        if ($this->discountLabel->getContents()) {
            $this->discountLabel->setRenderEnabled(true);
        }

    }

}
class PriceLabel extends Container {

    protected ?Link $availabilityLink = null;
    protected ?Meta $currencyMeta = null;

    protected ?Container $priceOld = null;
    protected ?Container $priceSell = null;

    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("price_info");

        $this->setAttribute("itemscope","");
        $this->setAttribute("itemprop", "offers");
        $this->setAttribute("itemtype", "https://schema.org/Offer");

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
     * Details page of this inventory
     * @var ProductURL
     */
    protected ProductURL $detailsURL;

    protected bool $product_linked_data_enabled = true;

    protected PriceLabel $priceLabel;

    protected ClosureComponent $wrap;
    protected Meta $positionMeta;

    protected ProductPhoto $productPhoto;

    public function __construct()
    {
        parent::__construct();

        $this->detailsURL = new ProductURL();

        $this->setAttribute("itemprop","itemListElement");
        $this->setAttribute("itemscope", "");
        $this->setAttribute("itemtype", "https://schema.org/ListItem");

        $this->setComponentClass("ProductListItem");
        $this->setTagName("li");

        $this->initPriceLabel();

        $this->positionMeta = new Meta();
        $this->positionMeta->setAttribute("itemprop","position");
        $this->items()->append($this->positionMeta);

        $closure = function() {
            $this->renderMeta();
            $this->productPhoto->render();
            $this->renderDetails();
        };

        $this->wrap = new ClosureComponent($closure,true, false);
        $this->wrap->setComponentClass("wrap");
        $this->wrap->setAttribute("itemprop", "item");
        $this->wrap->setAttribute("itemscope");
        $this->wrap->setAttribute("itemtype", "https://schema.org/Product");
        $this->wrap->setTagName("article");
        $this->items()->append($this->wrap);

        $this->productPhoto = new ProductPhoto($this);
        $this->productPhoto->setURL($this->detailsURL);
        $this->productPhoto->getImage()->getStorageItem()->className = ProductPhotosBean::class;
        $this->productPhoto->getImage()->getStorageItem()->setValueKey("ppID");
    }

    protected function initPriceLabel() : void
    {
        $this->priceLabel = new PriceLabel();
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

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/ProductListItem.css";
        return $arr;
    }

    public function setPhotoSize(int $width, int $height): void
    {
        $this->productPhoto->getImage()->setPhotoSize($width, $height);
    }

    public function getPhotoWidth(): int
    {
        return $this->productPhoto->getImage()->getPhotoWidth();
    }

    public function getPhotoHeight(): int
    {
        return $this->productPhoto->getImage()->getPhotoHeight();
    }

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->setAttribute("prodID", $this->data["prodID"]);

        $this->detailsURL->setData($data);
        $this->productPhoto->setData($data);
        $this->positionMeta->setContent($this->position);
    }

    protected function renderMeta()
    {
        echo "<meta itemprop='sku' content='" .$this->data["prodID"]."'>";
        echo "<meta itemprop='category' content='" . attributeValue($this->data["category_name"]) . "'>";
    }

    public function isPromo() : bool
    {
        return ((float)$this->data["price"] != (float)$this->data["sell_price"] && (float)$this->data["price"]>0);
    }

    /**
     * @return void
     */
    protected function renderDetails() : void
    {

        echo "<a class='details' href='{$this->getDetailsURL()}' >";

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
