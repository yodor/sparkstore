<?php
include_once("components/Container.php");
include_once("components/Link.php");
include_once("components/Meta.php");

class PriceLabel extends Container {

    protected ?Link $availabilityLink = null;
    protected ?Meta $validUntilMeta = null;
    protected ?Meta $currencyMeta = null;

    protected ?CurrencyLabel $priceOld = null;
    protected ?CurrencyLabel $priceSell = null;

    /**
     * Component used to show price values for products.
     * Includes old price and sell price.
     * Uses schema org Offer type
     */
    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("price_info");

        $this->setAttribute("itemscope");
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
        $this->currencyMeta->setContent(Spark::Get(StoreConfig::DEFAULT_CURRENCY));
        $this->items()->append($this->currencyMeta);

        $this->priceOld = new CurrencyLabel();
        $this->priceOld->addClassName("old");
        $this->items()->append($this->priceOld);

        $this->priceSell = new CurrencyLabel();
        $this->priceSell->addClassName("sell");
        $this->priceSell->span()->setAttribute("itemprop", "price");
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

//        $this->priceSell()->span()->removeAttribute("itemprop");
    }

    public function setCurrencyLabels(string $iso3, string $symbol) : void
    {
        $this->currency()->setContent($iso3);
        $this->priceOld()->setSymbol($symbol);
        $this->priceSell()->setSymbol($symbol);
        $this->setName($iso3);
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

?>