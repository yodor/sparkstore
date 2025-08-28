<?php
include_once("components/renderers/items/ListItem.php");
include_once("components/renderers/IHeadContents.php");
include_once("components/renderers/IPhotoRenderer.php");
include_once("storage/StorageItem.php");

include_once("store/utils/url/ProductURL.php");
include_once("utils/url/DataParameter.php");

include_once("store/beans/ProductPhotosBean.php");
class ProductDetails extends Action
{
    protected ProductListItem $item;

    protected Component $title;
    protected Container $info;
    protected Container $price;

    public function __construct(ProductListItem $item)
    {
        parent::__construct();
        $this->setComponentClass("details");
        $this->item = $item;

        $this->title = new Component(false);
        $this->title->setComponentClass("product_name");
        $this->title->setAttribute("itemprop", "name");
        $this->title->setTagName("h3");
        $this->title->setContents($item->getTitle());
        $this->items()->append($this->title);

        //additional info like brand, author, publisher etc - disabled by default
        $this->info = new Container(false);
        $this->info->setComponentClass("info");
        $this->info->setRenderEnabled(false);
        $this->items()->append($this->info);

        $this->price = new Container(false);
        $this->price->setComponentClass("price_label");
        $this->items()->append($this->price);

        if (DOUBLE_PRICE_ENABLED) {
            $priceLabelEUR = new PriceLabel();
            $priceLabelEUR->setName("EUR");
            $priceLabelEUR->addClassName("left");
            $priceLabelEUR->currency()->setContent("EUR");
            $this->price->items()->append($priceLabelEUR);
        }

        $priceLabel = new PriceLabel();
        $priceLabel->currency()->setContent(DEFAULT_CURRENCY);
        $priceLabel->setName(DEFAULT_CURRENCY);
        $this->price->items()->append($priceLabel);
    }

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->title->setContents($this->data["product_name"]);

        $this->price->setRenderEnabled(true);
        if ($this->data["sell_price"] < 1) {
            $this->price->setRenderEnabled(false);
            return;
        }

        $availability = "https://schema.org/OutOfStock";
        if ($this->data["stock_amount"]>0) {
            $availability = "https://schema.org/InStock";
        }

        if (DOUBLE_PRICE_ENABLED) {
            $eurPriceLabel = $this->price->items()->getByName("EUR");
            if ($eurPriceLabel instanceof PriceLabel) {
                $priceOld = "<BR>";
                if ($this->item->isPromo()) {
                    $priceOld = formatPrice( $this->data["price"] / DOUBLE_PRICE_RATE,"&euro;", true);
                }
                $eurPriceLabel->priceOld()->setContents($priceOld);

                $priceSell = formatPrice($this->data["sell_price"] / DOUBLE_PRICE_RATE, "", true);
                $priceSell = "<span class='currency'>&euro;&nbsp;</span><span itemprop='price'>$priceSell</span>";
                $eurPriceLabel->priceSell()->setContents($priceSell);

                $eurPriceLabel->availability()->setHref($availability);
            }
        }

        $priceLabel = $this->price->items()->getByName(DEFAULT_CURRENCY);
        if ($priceLabel instanceof PriceLabel) {
            $priceOld = "<BR>";
            if ($this->item->isPromo()) {
                $priceOld = formatPrice($this->data["price"], "лв", false);
            }
            $priceLabel->priceOld()->setContents($priceOld);

            $priceSell = formatPrice($this->data["sell_price"], "", false);
            $priceSell = "<span itemprop='price'>$priceSell</span><span class='currency'>&nbsp;лв.</span>";

            $priceLabel->priceSell()->setContents($priceSell);

            $priceLabel->availability()->setHref($availability);
        }
    }
}

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

class ProductListItem extends ListItem implements IHeadContents, IPhotoRenderer
{

    /**
     * Details page of this inventory
     * @var ProductURL
     */
    protected ProductURL $detailsURL;

    protected bool $product_linked_data_enabled = true;



    protected Container $wrap;

    protected Meta $skuMeta;
    protected Meta $categoryMeta;

    protected ProductPhoto $productPhoto;
    protected ProductDetails $productDetails;

    public function __construct()
    {
        parent::__construct();

        $this->detailsURL = new ProductURL();

        $this->setComponentClass("ProductListItem");
        $this->setTagName("li");

        //actial product itemtype
        $this->wrap = new Container(false);
        $this->wrap->setComponentClass("wrap");
        $this->wrap->setAttribute("itemprop", "item");
        $this->wrap->setAttribute("itemscope");
        $this->wrap->setAttribute("itemtype", "https://schema.org/Product");
        $this->wrap->setTagName("article");

        $this->skuMeta = new Meta();
        $this->skuMeta->setAttribute("itemprop", "sku");
        $this->wrap->items()->append($this->skuMeta);

        $this->categoryMeta = new Meta();
        $this->categoryMeta->setAttribute("itemprop", "category");
        $this->wrap->items()->append($this->categoryMeta);

        $this->productPhoto = $this->CreatePhoto();
        $this->productPhoto->setURL($this->detailsURL);
        $this->productPhoto->getImage()->getStorageItem()->className = ProductPhotosBean::class;
        $this->productPhoto->getImage()->getStorageItem()->setValueKey("ppID");
        $this->wrap->items()->append($this->productPhoto);

        $this->productDetails = $this->CreateDetails();
        $this->productDetails->setURL($this->detailsURL);
        $this->wrap->items()->append($this->productDetails);

        $this->items()->append($this->wrap);
    }

    protected function CreatePhoto() : ProductPhoto
    {
        return new ProductPhoto($this);
    }
    protected function CreateDetails() : ProductDetails
    {
        return new ProductDetails($this);
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
        $this->productDetails->setData($data);

        $this->skuMeta->setContent($this->data["prodID"]);
        $this->categoryMeta->setContent($this->data["category_name"]);
    }

    public function isPromo() : bool
    {
        return ((float)$this->data["price"] != (float)$this->data["sell_price"] && (float)$this->data["price"]>0);
    }

}

?>
