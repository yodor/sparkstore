<?php
include_once("beans/ConfigBean.php");
include_once("store/utils/cart/CartItem.php");
include_once("store/utils/cart/ZeroDiscount.php");
include_once("store/utils/cart/Delivery.php");

class Cart
{
    protected $items = array();

    protected $delivery = null;

    protected $note = "";

    protected $require_invoice = FALSE;

    protected $data = array();

    protected $discountProcessor = NULL;

    /**
     * @var null|Cart
     */
    protected static $instance = NULL;

    const SESSION_KEY = "spark_cart";

    const VERSION = "1.0";

    const NOTE_MAX_LENGTH = 255;

    static public function Instance(): Cart
    {
        if (self::$instance instanceof Cart) {
            debug("Returning already assigned instance");
            return self::$instance;
        }

        $cart = NULL;
        if (Session::Contains(Cart::SessionKey())) {
            debug("Trying to de-serialize Cart object from session");
            @$cart = unserialize(Session::Get(Cart::SessionKey()));

            if ($cart instanceof Cart) {
                debug("de-serialize success - calling delivery option initalization");
                $cart->getDelivery()->initialize();
                $cart->store();
            }
            else {
                $cart = NULL;
            }
        }

        if (is_null($cart)) {
            debug("Creating new instance of Cart");
            $cart = new Cart();
            $cart->store();
        }

        self::$instance = $cart;

        return self::$instance;
    }

    static public function SessionKey() : string
    {
        return Cart::SESSION_KEY."-".Cart::VERSION."-".SITE_TITLE;
    }

    private function __construct()
    {
        $this->items = array();
        $this->delivery = new Delivery();
        $this->delivery->initialize();

        $this->note = "";
        $this->require_invoice = false;
        $this->data = array();

        $this->discountProcessor = new ZeroDiscount();
        $this->discountProcessor->initialize();
    }

    public function store()
    {
        Session::Set(Cart::SessionKey(), serialize($this));
    }

    public function addItem(CartItem $item)
    {
        $itemID = $item->getID();
        if ($this->contains($itemID)) {
            $exist_item = $this->get($itemID);
            $exist_item->increment($item->getQuantity());
        }
        else {
            $this->items[$itemID] = $item;
        }
    }

    public function get(int $itemID): CartItem
    {
        if (isset($this->items[$itemID])) {
            return $this->items[$itemID];
        }
        throw new Exception("'$itemID' not found");
    }

    public function contains(int $itemID) : bool
    {
        return isset($this->items[$itemID]);
    }

    public function remove(int $itemID)
    {
        if (isset($this->items[$itemID])) {
            unset($this->items[$itemID]);
        }
    }

    public function removeItem(CartItem $item)
    {
        $itemID = $item->getID();
        if (isset($this->items[$itemID])) {
            unset($this->items[$itemID]);
        }
    }

    public function items(): array
    {
        return $this->items;
    }

    public function itemsCount(): int
    {
        $num_items = 0;
        foreach ($this->items as $itemID => $item) {
            if (!($item instanceof CartItem)) continue;
            $num_items += $item->getQuantity();
        }
        return $num_items;
    }

    public function clear()
    {
        $this->items = array();
    }

    public function getItemsTotal(): float
    {
        $items_total = 0.0;
        foreach ($this->items as $itemID => $item) {
            if (!($item instanceof CartItem)) continue;
            $items_total += $item->getLineTotal();
        }
        return $items_total;
    }

    public function setData(string $name, string $value)
    {
        $this->data[$name] = $value;
    }

    public function getData(string $name) : string
    {
        return $this->data[$name];
    }

    public function haveData(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    public function setNote(string $text)
    {
        $this->note = mb_substr($text, 0, Cart::NOTE_MAX_LENGTH);
    }

    public function getNote() : string
    {
        return $this->note;
    }

    public function setRequireInvoice(bool $mode)
    {
        $this->require_invoice = $mode;
    }

    public function getRequireInvoice() : bool
    {
        return $this->require_invoice;
    }

    public function getDelivery() : Delivery
    {
        return $this->delivery;
    }

    public function getDiscount() : IDiscountProcessor
    {
        return $this->discountProcessor;
    }

    public function setDiscount(IDiscountProcessor $proc) {
        $this->discountProcessor = $proc;
    }

}

?>