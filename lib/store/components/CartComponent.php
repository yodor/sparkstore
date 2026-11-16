<?php
include_once("components/Component.php");
include_once("components/renderers/cells/TableCell.php");

include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductsBean.php");

include_once("store/utils/SellableItem.php");
include_once("store/utils/cart/Cart.php");

include_once("store/components/PriceLabel.php");
include_once("input/renderers/Input.php");
include_once("components/Button.php");

class SummaryItem extends Container
{
    protected Container $label;
    protected Container $value;
    protected PriceLabel $price;

    public function __construct()
    {
        parent::__construct(false);

        $this->setComponentClass("summary");

        $this->label = new Container(false);
        $this->label->setComponentClass("label");
        $this->items()->append($this->label);

        $this->value = new Container(false);
        $this->value->setComponentClass("value");

        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $eurPrice = new PriceLabel();
            $eurPrice->setCurrencyLabels(Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY), Spark::Get(StoreConfig::DOUBLE_PRICE_SYMBOL));
            $eurPrice->disableLinkedData();

            $this->value->items()->append($eurPrice);
        }

        $this->price = new PriceLabel();
        $this->price->setCurrencyLabels(Spark::Get(StoreConfig::DEFAULT_CURRENCY), Spark::Get(StoreConfig::DEFAULT_CURRENCY_SYMBOL));
        $this->price->disableLinkedData();

        $this->value->items()->append($this->price);

        $this->items()->append($this->value);
    }
    protected function finalize(): void
    {
        parent::finalize();
        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $priceLabel = $this->value->items()->getByName(Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY));
            if ($priceLabel instanceof PriceLabel) {
                $priceLabel->priceSell()->setAmount($this->price->priceSell()->getAmount() / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE));
            }
        }

    }

    public function label(): Container
    {
        return $this->label;
    }
    public function value(): Container
    {
        return $this->value;
    }
    public function price() : PriceLabel
    {
        return $this->price;
    }
}

class CartListEmpty extends Container {
    public function __construct()
    {
        parent::__construct(false);
        $this->addClassName("empty");

        $h = new Component(false);
        $h->setContents(tr("Your shopping cart is empty."));
        $h->setAttribute("field", "cart_empty");
        $this->items()->append($h);
    }
}

class CartListHeading extends Container {
    public function __construct() {
        parent::__construct(false);
        $this->setComponentClass("heading");

        $h = new Component(false);
        $h->setComponentClass("");
        $h->setContents("#");
        $h->setAttribute("field", "position");
        $this->items()->append($h);

        $h = new Component(false);
        $h->setComponentClass("");
        $h->setContents(tr("Product"));
        $h->setAttribute("field", "product");
        $this->items()->append($h);

        $h = new Component(false);
        $h->setComponentClass("");
        $h->setContents(tr("Quantity"));
        $h->setAttribute("field", "qty");
        $this->items()->append($h);

        $h = new Component(false);
        $h->setComponentClass("");
        $h->setContents(tr("Price"));
        $h->setAttribute("field", "price");
        $this->items()->append($h);

        $h = new Component(false);
        $h->setComponentClass("");
        $h->setContents(tr("Total"));
        $h->setAttribute("field", "line-total");
        $this->items()->append($h);

    }
}
/**
 * POST form of one cart change (increment, decrement, remove) - a button with the item hash.
 * The request (CheckoutCart): cart=<change>&item=<hash>
 */
class CartActionForm extends Container {

    protected Input $item;
    protected Button $button;

    public function __construct(URL $postURL, string $change, string $contents, string $title)
    {
        parent::__construct(false);
        $this->setTagName("form");
        $this->setComponentClass("cart_action");
        $this->setAttribute("method", "post");
        $this->setAttribute("action", $postURL->toString());

        $this->items()->append(new Input("hidden", "cart", $change));

        $this->item = new Input("hidden", "item", "");
        $this->items()->append($this->item);

        $this->button = new Button(Button::TYPE_SUBMIT);
        $this->button->setComponentClass("");
        $this->button->setAttribute("action", $change);
        $this->button->setAttribute("title", $title);
        $this->button->setContents($contents);
        $this->items()->append($this->button);
    }

    public function setItem(string $hash) : void
    {
        $this->item->setValue($hash);
    }
}

class CartListItem extends Container {

    protected CartComponent $cartComponent;


    protected Action $actIncrement;
    protected Action $actDecrement;
    protected Action $actRemove;

    protected int $position = -1;

    /**
     * POST forms instead of the action links (CartComponent::setPostURL())
     * @var array<string, CartActionForm>
     */
    protected array $forms = array();

    protected TextComponent $qty;

    protected ImageStorage $image;
    protected TextComponent $label;

    protected PriceLabel $price;
    protected PriceLabel $lineTotal;

    public function __construct(CartComponent $parent)
    {
        parent::__construct(false);
        $this->setComponentClass("item");

        $this->cartComponent = $parent;

        $this->actRemove = new Action();
        $this->actRemove->setComponentClass("");
        $this->actRemove->setAttribute("action", "remove");
        $this->actRemove->setContents("&#8855;");
        $this->actRemove->setTitle("Remove");

        $tdPosition = new Container(false);
        $tdPosition->setComponentClass("");
        $tdPosition->setAttribute("field", "position");
        $tdPosition->items()->append($this->actRemove);
        $this->items()->append($tdPosition);

        $tdProduct = new Container(false);
        $tdProduct->setComponentClass("");
        $tdProduct->setAttribute("field", "product");

        $this->image = new ImageStorage();
        $this->image->setTagName("a");
        $this->image->setURL(new ProductURL());
        $this->image->image()->setPhotoSize(0,275);
        $tdProduct->items()->append($this->image);

        $this->label = new TextComponent();
        $tdProduct->items()->append($this->label);

        $this->items()->append($tdProduct);


        $tdQty = new Container(false);
        $tdQty->setComponentClass("");
        $tdQty->setAttribute("field", "qty");

        $tdQty->items()->append(new TextComponent(tr("Quantity"), "mobile-label"));

        $this->actDecrement = new Action();
        $this->actDecrement->setComponentClass("");
        $this->actDecrement->setAttribute("action", "decrement");
        $this->actDecrement->setContents("&#8854;");
        $this->actDecrement->setTitle(tr("Decrease quantity"));
        $tdQty->items()->append($this->actDecrement);

        $this->qty = new TextComponent();
        $this->qty->setComponentClass("cart_qty");
        $tdQty->items()->append($this->qty);

        $this->actIncrement = new Action();
        $this->actIncrement->setComponentClass("");
        $this->actIncrement->setAttribute("action", "increment");
        $this->actIncrement->setContents("&#8853;");
        $this->actIncrement->setTitle(tr("Increase quantity"));
        $tdQty->items()->append($this->actIncrement);

        $this->items()->append($tdQty);

        $tdPrice = new Container(false);
        $tdPrice->setComponentClass("");
        $tdPrice->setAttribute("field", "price");
        $tdPrice->items()->append(new TextComponent(tr("Price"), "mobile-label"));
        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $eurPrice = new PriceLabel();
            $eurPrice->setCurrencyLabels(Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY), Spark::Get(StoreConfig::DOUBLE_PRICE_SYMBOL));
            $eurPrice->disableLinkedData();
            $tdPrice->items()->append($eurPrice);
        }
        $this->price = new PriceLabel();
        $this->price->setCurrencyLabels(Spark::Get(StoreConfig::DEFAULT_CURRENCY), Spark::Get(StoreConfig::DEFAULT_CURRENCY_SYMBOL));
        $this->price->disableLinkedData();
        $tdPrice->items()->append($this->price);
        $this->items()->append($tdPrice);

        $tdLineTotal = new Container(false);
        $tdLineTotal->setComponentClass("");
        $tdLineTotal->setAttribute("field", "line-total");
        $tdLineTotal->items()->append(new TextComponent(tr("Total"),"mobile-label"));

        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $eurLine = new PriceLabel();
            $eurLine->setCurrencyLabels(Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY), Spark::Get(StoreConfig::DOUBLE_PRICE_SYMBOL));
            $eurLine->disableLinkedData();
            $tdLineTotal->items()->append($eurLine);
        }

        $this->lineTotal = new PriceLabel();
        $this->lineTotal->setCurrencyLabels(Spark::Get(StoreConfig::DEFAULT_CURRENCY), Spark::Get(StoreConfig::DEFAULT_CURRENCY_SYMBOL));
        $this->lineTotal->disableLinkedData();
        $tdLineTotal->items()->append($this->lineTotal);
        $this->items()->append($tdLineTotal);

        //changes as POST forms in place of the actions - the actions only show the position (not modify enabled)
        $postURL = $parent->getPostURL();
        if ($parent->isModifyEnabled()) {
            foreach (array("remove" => $this->actRemove, "decrement" => $this->actDecrement, "increment" => $this->actIncrement) as $change => $action) {
                $form = new CartActionForm($postURL, $change, $action->getContents(), $action->getTitle());
                $this->forms[$change] = $form;
                $cell = ($change === "remove") ? $tdPosition : $tdQty;
                $cell->items()->insert($form, $cell->items()->indexOf($action));
                $action->setRenderEnabled(false);
            }
        }

    }
    protected function finalize(): void
    {
        parent::finalize();
        if (!$this->cartComponent->isModifyEnabled()) {
            $this->actRemove->setContents(($this->position+1));
            $this->actRemove->setTagName("span");
            $this->actRemove->removeAttribute("href");

            $this->actIncrement->setRenderEnabled(false);
            $this->actDecrement->setRenderEnabled(false);
        }
        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $tdPrice = $this->items()->getByAttribute("price", "field");
            $eurPrice = $tdPrice->items()->getByName(Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY));
            if ($eurPrice instanceof PriceLabel) {
                if ($this->price->priceOld()->getAmount()) {
                    $eurPrice->priceOld()->setAmount($this->price->priceOld()->getAmount() / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE));
                }
                $eurPrice->priceSell()->setAmount($this->price->priceSell()->getAmount() / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE));
            }

            $tdLine = $this->items()->getByAttribute("line-total", "field");
            $eurPrice = $tdLine->items()->getByName(Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY));
            if ($eurPrice instanceof PriceLabel) {
                $eurPrice->priceSell()->setAmount($this->lineTotal->priceSell()->getAmount() / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE));
            }
        }
    }

    public function setEntry(CartEntry $cartEntry, int $position) : void
    {
        $item = $cartEntry->getItem();

        $this->position = $position;

        //the forms of the changes
        foreach ($this->forms as $form) {
            $form->setItem($item->hash());
        }

        //image
        $url = $this->image->getURL();
        if ($url instanceof ProductURL) {
            $url->setProductID($item->getProductID());
            $url->setProductName($item->getTitle());
        }
        //a product without a photo: no image
        $photo = $item->getMainPhoto();
        if ($photo instanceof StorageItem) {
            $this->image->image()->setStorageItem($photo);
        }
        else {
            $this->image->setRenderEnabled(false);
        }



        //title
        $title = Spark::AttributeValue($item->getTitle()) . "<BR>";
        $variants = $item->getVariantNames();
        foreach ($variants as $idx=>$variantName) {
            $variantItem = $item->getVariant($variantName);

            if ($variantItem instanceof VariantItem) {
                $title.= Spark::AttributeValue($variantName.": ".$variantItem->getSelected())."<BR>";
            }
        }
        $this->label->setContents($title);

        $this->qty->setContents($cartEntry->getQuantity());

        $this->price->priceOld()->setAmount(null);
        if ($item->isPromotion()) {
            $this->price->priceOld()->setAmount($item->getPriceInfo()->getOldPrice());
        }
        $this->price->priceSell()->setAmount($item->getPriceInfo()->getSellPrice());

        $this->lineTotal->priceOld()->setAmount(null);
        $this->lineTotal->priceSell()->setAmount($cartEntry->getLineTotal());
    }
}
class CartComponent extends Container implements IHeadContents
{

    protected bool $modify_enabled = false;

    /**
     * The changes (increment, decrement, remove) are POST forms to this URL - the cart of the checkout module
     * @var URL
     */
    protected URL $postURL;

    protected float $total = 0.0;
    protected float $order_total = 0.0;
    protected float $delivery_price = 0.0;
    protected float $discount_amount = 0.0;

    public function __construct()
    {
        parent::__construct(false);
        $this->postURL = new URL(Spark::Get(Config::LOCAL) . "/checkout/cart");
    }


    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/CartComponent.css";
        return $arr;
    }

    public function setModifyEnabled(bool $mode) : void
    {
        $this->modify_enabled = $mode;
    }

    /**
     * Send the changes as POST forms to '$url' (cart=<change>&item=<hash>) - default /checkout/cart
     * @param URL $url
     * @return void
     */
    public function setPostURL(URL $url) : void
    {
        $this->postURL = $url;
    }

    public function getPostURL() : URL
    {
        return $this->postURL;
    }

    public function isModifyEnabled() : bool
    {
        return $this->modify_enabled;
    }

    public function initialize(): void
    {

        $cart = Cart::Instance();

        $this->items()->clear();
        $this->items()->append(new CartListHeading());

        $total = 0;

        if ($cart->itemsCount() == 0) {
            $this->items()->append(new CartListEmpty());
        }
        else {

            $items_listed = 0;
            $num_items_total = 0;

            $itemsAll = $cart->items();

            foreach ($itemsAll as $itemHash => $cartEntry) {
                if (!($cartEntry instanceof CartEntry)) continue;

                $item = new CartListItem($this);
                $item->setEntry($cartEntry, $items_listed);
                $this->items()->append($item);

                $total += $cartEntry->getLineTotal();
                $num_items_total += ($cartEntry->getQuantity());
                $items_listed++;
            }
        }

        $order_total = $total;

        if ($total > 0) {
            $this->total = $total;

            $summaryItem = new SummaryItem();
            $summaryItem->addClassName("items_total");
            $summaryItem->label()->setContents(tr("Products Total"));
            $summaryItem->price()->priceSell()->setAmount($total);
            $this->items()->append($summaryItem);


            $discount = $cart->getDiscount();
            $this->discount_amount = $discount->amount();

            $order_total = (float)$order_total - (float)$this->discount_amount;

            if ($discount->amount()) {
                $discountItem = new SummaryItem();
                $discountItem->addClassName("discount");
                $discountItem->label()->setContents(tr("Discount"));
                $discountItem->price()->priceSell()->setAmount($discount->amount());
                $this->items()->append($discountItem);
            }

            $selected_courier = $cart->getDelivery()->getSelectedCourier();
            $selected_option = NULL;
            if ($selected_courier) {
                $selected_option = $selected_courier->getSelectedOption();
            }

            //delivery: not in the cart editor (modify enabled) - only once a delivery is chosen, in the order steps
            if ($selected_option != NULL && !$this->modify_enabled) {

                $deliveryItem = new SummaryItem();
                $deliveryItem->addClassName("delivery");
                $deliveryItem->label()->setContents(tr("Delivery"));
                $this->items()->append($deliveryItem);

                $delivery_price = $selected_option->getPrice();

                if ($delivery_price>0) {
                    $deliveryItem->price()->priceSell()->setAmount($delivery_price);
                }
                else {
                    $deliveryItem->value()->items()->clear();
                    if ($delivery_price==0) {
                        $deliveryItem->value()->items()->append(new TextComponent(tr("Free")));
                    }
                    else {
                        $deliveryItem->value()->items()->append(new TextComponent(tr("Courier plan dependent")));
                    }
                    $delivery_price = 0;
                }

                $this->delivery_price = $delivery_price;
                $order_total = $order_total + $this->delivery_price;
            }

            $orderTotal = new SummaryItem();
            $orderTotal->addClassName("order_total");
            $orderTotal->label()->setContents(tr("Order Total"));
            $orderTotal->price()->priceSell()->setAmount($order_total);
            $this->items()->append($orderTotal);
        }

    }

}