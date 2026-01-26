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
            $priceLabelEUR->setCurrencyLabels("EUR", "&euro;");
            $this->price->items()->append($priceLabelEUR);
        }

        $priceLabel = new PriceLabel();
        $priceLabel->setName(DEFAULT_CURRENCY);
        $priceLabel->setCurrencyLabels(DEFAULT_CURRENCY, "лв.");
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
                $eurPriceLabel->availability()->setHref($availability);

                $eurPriceLabel->priceOld()->setAmount(null);
                if ($this->item->isPromo()) {
                    $eurPriceLabel->priceOld()->setAmount(($this->data["price"] / DOUBLE_PRICE_RATE));
                }

                $eurPriceLabel->priceSell()->setAmount(($this->data["sell_price"] / DOUBLE_PRICE_RATE));

                if (!$this->item->isProductLinkedDataEnabled()) {
                    $eurPriceLabel->disableLinkedData();
                }
            }

        }

        $priceLabel = $this->price->items()->getByName(DEFAULT_CURRENCY);
        if ($priceLabel instanceof PriceLabel) {
            $priceLabel->availability()->setHref($availability);

            $priceLabel->priceOld()->setAmount(null);
            if ($this->item->isPromo()) {
                $priceLabel->priceOld()->setAmount($this->data["price"]);
            }

            $priceLabel->priceSell()->setAmount($this->data["sell_price"]);

            if (!$this->item->isProductLinkedDataEnabled()) {
                $priceLabel->disableLinkedData();
            }
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

        if ($this->item->isPromo()) {
            $discountPercent = $this->item->getDiscountPercent();
            if ($discountPercent>0) {
                $this->discountLabel->setContents(" -".$discountPercent."%");
            }
            else {
                $this->discountLabel->setContents("Промо");
            }
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
class CurrencyLabel extends LabelSpan {

    protected string $symbol = "";
    protected ?float $value = null;

    public function __construct()
    {
        parent::__construct();
        $this->label()->setTagName("span");
        $this->setComponentClass("price");
        $this->label()->setComponentClass("currency");
    }

    public function setAmount(?float $amount) : void
    {
        $this->value = $amount;

        if ($amount==null) {
            $this->label()->setContents("");
            $this->span()->setContents("");
            return;
        }
        $this->span()->setContents(sprintf("%0.2f", $amount));
        $this->label()->setContents($this->symbol);
    }

    public function getAmount() : ?float
    {
        return $this->value;
    }

    public function setSymbol(string $currency) : void
    {
        $this->symbol = $currency;
    }
    public function getSymbol() : string
    {
        return $this->symbol;
    }
}
class PriceLabel extends Container {

    protected ?Link $availabilityLink = null;
    protected ?Meta $validUntilMeta = null;
    protected ?Meta $currencyMeta = null;

    protected ?CurrencyLabel $priceOld = null;
    protected ?CurrencyLabel $priceSell = null;

    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("price_info");

        $this->setAttribute("itemscope","");
        $this->setAttribute("itemprop", "offers");
        $this->setAttribute("itemtype", "https://schema.org/Offer");

        $priceValidUntil = date("Y-m-d", strtotime("+1 year"));
        $this->validUntilMeta = new Meta();
        $this->validUntilMeta->setAttribute("itemprop", "priceValidUntil");
        $this->validUntilMeta->setContent($priceValidUntil);
        $this->items()->append($this->validUntilMeta);

        $this->availabilityLink = new Link();
        $this->availabilityLink->removeAttribute("rel");
        $this->availabilityLink->setAttribute("itemprop", "availability");
        $this->items()->append($this->availabilityLink);

        $this->currencyMeta = new Meta();
        $this->currencyMeta->setAttribute("itemprop", "priceCurrency");
        $this->currencyMeta->setContent(DEFAULT_CURRENCY);
        $this->items()->append($this->currencyMeta);

        $this->priceOld = new CurrencyLabel();
        $this->priceOld->addClassName("old");
        $this->items()->append($this->priceOld);

        $this->priceSell = new CurrencyLabel();
        $this->priceSell->addClassName("sell");
        $this->items()->append($this->priceSell);

    }

    public function disableLinkedData() : void
    {
        $this->removeAttribute("itemprop");
        $this->removeAttribute("itemscope");
        $this->removeAttribute("itemtype");
        $this->availability()->setRenderEnabled(false);
        $this->validUntil()->setRenderEnabled(false);
        $this->currency()->setRenderEnabled(false);
        $this->priceSell()->removeAttribute("itemprop");
        $this->priceOld()->removeAttribute("itemprop");
    }
    public function setCurrencyLabels(string $iso3, string $symbol) : void
    {
        $this->currency()->setContent($iso3);
        $this->priceOld()->setSymbol($symbol);
        $this->priceSell()->setSymbol($symbol);
    }

    public function validUntil() : Meta
    {
        return $this->validUntilMeta;
    }

    public function availability() : Link
    {
        return $this->availabilityLink;
    }

    public function currency() : Meta
    {
        return $this->currencyMeta;
    }

    public function priceOld() : CurrencyLabel
    {
        return $this->priceOld;
    }
    public function priceSell() : CurrencyLabel
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

    protected bool $productLinkedDataEnabled = true;



    protected Container $wrap;

    protected Meta $skuMeta;
    protected Meta $categoryMeta;
    protected Meta $urlMeta;

    protected ProductPhoto $productPhoto;
    protected ProductDetails $productDetails;

    protected string $defaultItemType = "https://schema.org/Product";

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
        $this->wrap->setAttribute("itemtype", $this->defaultItemType);
        $this->wrap->setTagName("article");

        $this->skuMeta = new Meta();
        $this->skuMeta->setAttribute("itemprop", "sku");
        $this->wrap->items()->append($this->skuMeta);

        $this->categoryMeta = new Meta();
        $this->categoryMeta->setAttribute("itemprop", "category");
        $this->wrap->items()->append($this->categoryMeta);

        $this->urlMeta = new Meta();
        $this->urlMeta->setAttribute("itemprop", "url");
        $this->wrap->items()->append($this->urlMeta);

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
        $this->urlMeta->setContent($this->detailsURL->fullURL());
    }

    public function isPromo() : bool
    {
        return ((float)$this->data["price"] != (float)$this->data["sell_price"] && (float)$this->data["price"]>0);
    }
    public function getDiscountPercent(): float
    {
        $discountPercent = $this->data["discount_percent"];
        if ($discountPercent==0) {
            if ($this->isPromo()) {
                $discountPercent = 100.00 - ((float)($this->data["sell_price"] / $this->data["price"]) * 100.00);
            }
        }
        return round($discountPercent,2);
    }

    public function isProductLinkedDataEnabled() : bool
    {
        return $this->productLinkedDataEnabled;
    }
    public function disableProductLinkedData() : void
    {
        $this->productLinkedDataEnabled = false;
        $this->wrap->removeAttribute("itemtype");
        $this->skuMeta->setRenderEnabled(false);
        $this->categoryMeta->setRenderEnabled(false);
    }

}

?>
