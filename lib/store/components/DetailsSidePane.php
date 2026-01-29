<?php

class SidePaneGroup extends Container
{
    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("group");
        $this->setTagName("section");

    }
}
class NameValueItem extends LabelSpan
{

    public function __construct()
    {
        parent::__construct();
        $this->setComponentClass("item");
        $this->wrapper_enabled = false;

        $this->label->setComponentClass("name");
        $this->label->setTagName("dt");

        $this->span->setComponentClass("value");
        $this->span->setTagName("dd");

    }
    public function setNameValue(string $name, string $value) : void
    {
        $this->label->setContents($name);
        $this->span->setContents($value);
    }
}
class NameValueList extends Container
{
    public function __construct()
    {
        parent::__construct(false);
        $this->setTagName("dl");
        $this->setComponentClass("NameValueList");
        $this->setClassName("viewport");
    }

    public function addItem(string $name, string $value) : NameValueItem
    {
        $item = new NameValueItem();
        $item->setNameValue($name, $value);
        $this->items()->append($item);
        return $item;
    }
}
class CartButton extends Action
{
    protected ?Component $_title = null;

    public function __construct()
    {
        parent::__construct();
        $this->setComponentClass("button");
        $icon = new Component(false);
        $icon->setTagName("SPAN");
        $icon->setComponentClass("icon");
        $this->items()->append($icon);

        $this->_title = new Component(false);
        $this->_title->setTagName("SPAN");
        $this->items()->append($this->_title);
    }

    protected function processAttributes(): void
    {
        parent::processAttributes();
        $this->_title->setContents($this->getTitle());
    }

}
class DetailsSidePane extends Container
{
    const string BUTTON_QUERY_PRODUCT = "Query Product";
    const string BUTTON_NOTIFY_INSTOCK = "Notify Instock";
    const string BUTTON_PHONE_ORDER = "Phone Order";
    const string BUTTON_FAST_ORDER = "Fast Order";
    const string BUTTON_CART_ORDER = "Cart Order";

    const string BUTTON_PAYMENT_TBI = "TBI";
    const string BUTTON_PAYMENT_TBI_FUSION = "TBI_FUSION";
    const string BUTTON_PAYMENT_UNICREDIT = "UNICREDIT";

    protected array $buttons = array();

    //TBI store UID if defined
    protected array $crpayments = array();

    /**
     * @var SellableItem|null
     */
    protected ?SellableItem $sellable = null;

    public function __construct(SellableItem $item)
    {
        parent::__construct(false);

        $this->setComponentClass("side_pane");

        $this->sellable = $item;

        $priceInfo = $this->sellable->getPriceInfo();

        if ($priceInfo->getSellPrice()==0) {
            $this->setAttribute("nonsellable");
        }


    }

    public function initialize() : void
    {
        $this->initCaptionGroup();
        $this->initDetailsGroup();
        $this->initStockAmountGroup();
        $this->initAttributesGroup();
        $this->initVariantsGroup();
        $this->initPricingGroup();
        $this->initCartLinkGroup();
    }

    public function initializeCartButtons() : void
    {
        $this->setButtonEnabled(DetailsSidePane::BUTTON_QUERY_PRODUCT, true);
        $this->setButtonEnabled(DetailsSidePane::BUTTON_NOTIFY_INSTOCK, true);
        $this->setButtonEnabled(DetailsSidePane::BUTTON_FAST_ORDER, true);
        $this->setButtonEnabled(DetailsSidePane::BUTTON_PHONE_ORDER, true);
        $this->setButtonEnabled(DetailsSidePane::BUTTON_CART_ORDER, true);
    }

    public function initializePaymentButtons() : void
    {
        $this->crpayments[DetailsSidePane::BUTTON_PAYMENT_UNICREDIT] = new UniCreditPaymentButton($this->sellable);
        $this->crpayments[DetailsSidePane::BUTTON_PAYMENT_TBI] = new TBICreditPaymentButton($this->sellable);
        $this->crpayments[DetailsSidePane::BUTTON_PAYMENT_TBI_FUSION] = new TBIFusionPaymentButton($this->sellable);
    }

    public function disableCartButtons() : void
    {
        foreach ($this->buttons as $button_name=>$button) {
            $this->setButtonEnabled($button_name, false);
        }
    }

    public function disablePaymentButtons() : void
    {
        foreach ($this->crpayments as $payment_name=>$button) {
            $this->setPaymentEnabled($payment_name, false);
        }
    }

    public function setButtonEnabled(string $button_name, bool $mode) : void
    {
        if ($mode) {
            $this->buttons[$button_name] = true;
            switch ($button_name) {
                case DetailsSidePane::BUTTON_QUERY_PRODUCT:
                    $this->buttons[$button_name] = new QueryProductFormResponder($this->sellable);
                    break;
                case DetailsSidePane::BUTTON_NOTIFY_INSTOCK:
                    $this->buttons[$button_name] = new NotifyInstockFormResponder($this->sellable);
                    break;
                case DetailsSidePane::BUTTON_FAST_ORDER:
                    $this->buttons[$button_name] = new OrderProductFormResponder($this->sellable);
                    break;
            }
        }
        else {
            if (isset($this->buttons[$button_name])) {
                unset($this->buttons[$button_name]);
            }
        }
    }

    public function isButtonEnabled(string $button_name) : bool
    {
        return isset($this->buttons[$button_name]);
    }

    public function setPaymentEnabled(string $payment_name, bool $mode) : void
    {
        if ($mode) {

            switch ($payment_name) {
                case self::BUTTON_PAYMENT_TBI_FUSION:
                    $this->crpayments[$payment_name] = new TBIFusionPaymentButton($this->sellable);
                    break;
                case self::BUTTON_PAYMENT_TBI:
                    $this->crpayments[$payment_name] = new TBICreditPaymentButton($this->sellable);
                    break;
                case self::BUTTON_PAYMENT_UNICREDIT:
                    $this->crpayments[$payment_name] = new UniCreditPaymentButton($this->sellable);
                    break;

            }
        }
        else {
            if (isset($this->crpayments[$payment_name])) {
                unset($this->crpayments[$payment_name]);
            }
        }
    }

    public function isPaymentEnabled(string $button_name) : bool
    {
        return isset($this->crpayments[$button_name]);
    }

    public function addPaymentButton(CreditPaymentButton $button, string $id) : void
    {
        $this->crpayments[$id] = $button;
    }



    protected function initCaptionGroup() : void
    {
        $grp = new SidePaneGroup();
        $grp->setClassName("caption");

        $grp->buffer()->start();
        echo "<div class='item product_name'>";
        echo "<h1 class='value'>". $this->sellable->getTitle() . "</h1>";
        echo "</div>";
        $grp->buffer()->end();

        $this->items()->append($grp);
    }

    public function getGroup(string $name) : SidePaneGroup
    {
        $cmp = $this->items()->getByClassName($name);
        if ($cmp instanceof SidePaneGroup) {
            return $cmp;
        }
        throw new Exception("SidePaneGroup $name does not exist");
    }

    protected function initDetailsGroup() : void
    {
        $grp = new SidePaneGroup();
        $grp->setClassName("details");
        $this->items()->append($grp);
    }

    protected function initStockAmountGroup() : void
    {
        $grp = new SidePaneGroup();
        $grp->setClassName("stock_amount");
        $this->items()->append($grp);
    }

    protected function initAttributesGroup() : void
    {
        $grp = new SidePaneGroup();
        $grp->setClassName("attributes");

        $list = new NameValueList();
        $grp->items()->append($list);

        $attributes = $this->sellable->getAttributes();
        foreach ($attributes as $name=>$value) {
            if ($name && $value) {
                $list->addItem($name, $value);
            }
        }

        $this->items()->append($grp);
    }

    protected function initVariantsGroup() : void
    {
        $grp = new SidePaneGroup();
        $grp->setClassName("variants");

        $list = new NameValueList();
        $grp->items()->append($list);

        $variantNames = $this->sellable->getVariantNames();

        foreach ($variantNames as $idx=>$variantName) {
            $item = new NameValueItem();
            $item->setClassName("variant");
            $item->setAttribute("name", $variantName);
            $item->setNameValue($variantName, "");

            //TODO: listing style
            $vitem = $this->sellable->getVariant($variantName);
            if ($vitem instanceof VariantItem) {
                $parameterList = new Container(false);
                $parameterList->setComponentClass("list parameters");
                $item->items()->append($parameterList);

                $parameters = $vitem->getParameters();
                foreach ($parameters as $pos=>$option_value) {
                    $value = attributeValue($option_value);
                    $parameter = new Component(false);
                    $parameter->setComponentClass("parameter");
                    $parameter->setAttribute("pos", $pos);
                    $parameter->setAttribute("value", $value);
                    $parameter->setAttribute("onClick","javascript:selectVariantParameter(this)");
                    $parameter->setContents($option_value);
                    $parameterList->items()->append($parameter);
                }

            }

            $list->items()->append($item);
        }


        $this->items()->append($grp);
    }

    protected function initPricingGroup() : void
    {

        $priceInfo = $this->sellable->getPriceInfo();
        $stock_amount = $this->sellable->getStockAmount();

        $grp = new SidePaneGroup();
        $grp->setClassName("pricing");

        if ($stock_amount>0) {
            $grp->setAttribute("in_stock", $stock_amount);
        }
        else {
            $grp->setAttribute("no_stock", "");
        }

        if (DOUBLE_PRICE_ENABLED) {

            $item = new PriceLabel();
            $item->addClassName("item");
            $item->setCurrencyLabels("EUR", "&euro;");
            $item->disableLinkedData();

            $item->priceOld()->setAmount(null);
            if ($this->sellable->isPromotion()) {
                $item->priceOld()->setAmount($priceInfo->getOldPrice() / DOUBLE_PRICE_RATE);
            }
            $item->priceSell()->setAmount(null);
            if ($priceInfo->getSellPrice() > 0) {
                $item->priceSell()->setAmount($priceInfo->getSellPrice()/DOUBLE_PRICE_RATE);
            }
            $grp->items()->append($item);
        }

        $item = new PriceLabel();
        $item->addClassName("item");
        $item->setCurrencyLabels(DEFAULT_CURRENCY, DEFAULT_CURRENCY_SYMBOL);
        $item->disableLinkedData();

        $item->priceOld()->setAmount(null);
        if ($this->sellable->isPromotion()) {
            $item->priceOld()->setAmount($priceInfo->getOldPrice());
        }
        $item->priceSell()->setAmount(null);
        if ($priceInfo->getSellPrice() > 0) {
            $item->priceSell()->setAmount($priceInfo->getSellPrice());
        }
        $grp->items()->append($item);


        $this->items()->append($grp);

    }

    protected function initCartLinkGroup() : void
    {

        $grp = new SidePaneGroup();
        $grp->setClassName("cart_link");

        $stock_amount = $this->sellable->getStockAmount();
        if ($stock_amount>0) {
            $grp->setAttribute("in_stock", $stock_amount);
        }
        else {
            $grp->setAttribute("no_stock", "");
        }

        if ($stock_amount<1) {
            if ($this->isButtonEnabled(DetailsSidePane::BUTTON_NOTIFY_INSTOCK)) {
                $btnNoStock = new CartButton();
                $btnNoStock->setClassName("nostock");
                $btnNoStock->setURL(new URL("javascript:showNotifyInstockForm()"));
                $btnNoStock->setTitle(tr("Уведоми ме при наличност"));
                $grp->items()->append($btnNoStock);
            }
        }
        else {

            if ($this->isButtonEnabled(DetailsSidePane::BUTTON_FAST_ORDER)) {
                $btnFastOrder = new CartButton();
                $btnFastOrder->setClassName("cart_add");
                $btnFastOrder->addClassName("fast");
                $btnFastOrder->setURL(new URL("javascript:showOrderProductForm()"));
                $btnFastOrder->setTitle(tr("Бърза поръчка"));
                $grp->items()->append($btnFastOrder);
            }
            if ($this->isButtonEnabled(DetailsSidePane::BUTTON_CART_ORDER)) {
                $btnCartAdd = new CartButton();
                $btnCartAdd->setClassName("cart_add");
                $btnCartAdd->setURL(new URL("javascript:addToCart()"));
                $btnCartAdd->setTitle(tr("Купи"));
                $grp->items()->append($btnCartAdd);
            }
        }


        $config = ConfigBean::Factory();
        $config->setSection("store_config");
        $phone = $config->get("phone_orders", "");
        if ($phone) {
            if ($this->isButtonEnabled(DetailsSidePane::BUTTON_PHONE_ORDER)) {
                $btnPhoneOrder = new CartButton();
                $btnPhoneOrder->setClassName("order_phone");
                $btnPhoneOrder->setURL(new URL("tel:$phone"));
                $btnPhoneOrder->setTitle($phone);
                $grp->items()->append($btnPhoneOrder);
            }
        }

        if ($this->isButtonEnabled(DetailsSidePane::BUTTON_QUERY_PRODUCT)) {
            $btnQueryProduct = new CartButton();
            $btnQueryProduct->setClassName("query_product");
            $btnQueryProduct->setURL(new URL("javascript:showProductQueryForm()"));
            $btnQueryProduct->setTitle(tr("Запитване") );
            $grp->items()->append($btnQueryProduct);
        }

        $this->initPaymentButtons($grp);

        $this->items()->append($grp);
    }

    protected function initPaymentButtons(SidePaneGroup $grp) : void
    {

        //payment modules
        foreach ($this->crpayments as $idx=>$object) {
            $class = get_class($object);

            if (!($object instanceof CreditPaymentButton)) continue;

            if (!$object->isEnabled()) continue;

            $buttonModule = new Container(false);
            $buttonModule->setComponentClass("module");
            $buttonModule->setClassName($class);


            $buttonModule->buffer()->start();
            try {
                $object->checkStockPrice();
                $object->renderButton();
            }
            catch (Exception $e) {
                $buttonModule->buffer()->clear();
                debug("Error rendering credit payment button '$class': ".$e->getMessage());
            }

            $buttonModule->buffer()->end();
            $grp->items()->append($buttonModule);

        }

    }

}
?>