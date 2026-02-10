<?php
include_once("components/renderers/items/ListItem.php");
include_once("components/renderers/IHeadContents.php");
include_once("components/renderers/IPhotoRenderer.php");
include_once("storage/StorageItem.php");

include_once("store/utils/url/ProductURL.php");
include_once("utils/url/DataParameter.php");

include_once("store/beans/ProductPhotosBean.php");

include_once("store/components/PriceLabel.php");
include_once("store/components/CurrencyLabel.php");

class ProductDetails extends Action
{
    protected ProductListItem $item;

    protected Component $title;
    protected Container $info;
    protected Container $priceLabel;

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

        //additional info like brand, author, publisher etc. - disabled by default
        $this->info = new Container(false);
        $this->info->setComponentClass("info");
        $this->info->setRenderEnabled(false);
        $this->items()->append($this->info);

        $this->priceLabel = new Container(false);
        $this->priceLabel->setComponentClass("price_label");
        $this->items()->append($this->priceLabel);

        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $eurLabel = new PriceLabel();
            $eurLabel->addClassName("left");
            $eurLabel->setCurrencyLabels(Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY), Spark::Get(StoreConfig::DOUBLE_PRICE_SYMBOL));
            $this->priceLabel->items()->append($eurLabel);
        }

        $curLabel = new PriceLabel();
        $curLabel->setCurrencyLabels(Spark::Get(StoreConfig::DEFAULT_CURRENCY), Spark::Get(StoreConfig::DEFAULT_CURRENCY_SYMBOL));
        $this->priceLabel->items()->append($curLabel);

    }

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->title->setContents($this->data["product_name"]);

        $this->priceLabel->setRenderEnabled(true);
        if ($this->data["sell_price"] < 1) {
            $this->priceLabel->setRenderEnabled(false);
            return;
        }

        $availability = "https://schema.org/OutOfStock";
        if ($this->data["stock_amount"]>0) {
            $availability = "https://schema.org/InStock";
        }
        $validity = date("Y-m-d", strtotime("+1 year"));

        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $eurLabel = $this->priceLabel->items()->getByName(Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY));
            if ($eurLabel instanceof PriceLabel) {
                $eurLabel->availability()->setHref($availability);
                $eurLabel->validUntil()->setContent($validity);
                $eurLabel->priceOld()->setAmount(null);
                if ($this->item->isPromo()) {
                    $eurLabel->priceOld()->setAmount(($this->data["price"] / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE)));
                }

                $eurLabel->priceSell()->setAmount(($this->data["sell_price"] / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE)));

                if (!$this->item->isProductLinkedDataEnabled()) {
                    $eurLabel->disableLinkedData();
                }
            }

        }

        $defLabel = $this->priceLabel->items()->getByName(Spark::Get(StoreConfig::DEFAULT_CURRENCY));
        if ($defLabel instanceof PriceLabel) {
            $defLabel->availability()->setHref($availability);
            $defLabel->validUntil()->setContent($validity);

            $defLabel->priceOld()->setAmount(null);
            if ($this->item->isPromo()) {
                $defLabel->priceOld()->setAmount($this->data["price"]);
            }

            $defLabel->priceSell()->setAmount($this->data["sell_price"]);

            if (!$this->item->isProductLinkedDataEnabled()) {
                $defLabel->disableLinkedData();
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
        $arr[] = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/ProductListItem.css";
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
