<?php
include_once("beans/ConfigBean.php");
include_once("store/utils/cart/CartItem.php");
include_once("store/utils/cart/ZeroDiscount.php");
include_once("store/utils/cart/Delivery.php");
include_once("store/utils/cart/ICartListener.php");

class Cart
{
    protected $items = array();

    protected $delivery = NULL;

    protected $note = "";

    protected $require_invoice = FALSE;

    protected $data = array();

    protected $discountProcessor = NULL;

    protected $cartListeners = NULL;

    /**
     * @var null|Cart
     */
    protected static $instance = NULL;
    protected static $session_key = NULL;

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

    static public function SessionKey(): string
    {
        if (self::$session_key) {
            return self::$session_key;
        }
        self::$session_key = md5(Cart::SESSION_KEY . "-" . Cart::VERSION . "-" . SITE_TITLE);
        return self::$session_key;
    }

    private function __construct()
    {
        $this->items = array();
        $this->delivery = new Delivery();
        $this->delivery->initialize();

        $this->note = "";
        $this->require_invoice = FALSE;
        $this->data = array();

        $this->discountProcessor = new ZeroDiscount();
        $this->discountProcessor->initialize();

        $this->cartListeners = array();
    }

    public function addCartListener(ICartListener $listener)
    {
        $this->cartListeners[] = $listener;
    }

    public function store()
    {
        Session::Set(Cart::SessionKey(), serialize($this));
    }

    /**
     * @param Closure $closure
     * @param string $oprName
     * @param ?CartItem $item
     */
    protected function emit(Closure $closure, string $oprName, ?CartItem $item)
    {
        if (is_array($this->cartListeners) && count($this->cartListeners)>0) {
            foreach ($this->cartListeners as $idx => $listener) {
                if ($listener instanceof ICartListener) {
                    $listener->before($oprName, $item, $this);
                    $closure();
                    $listener->after($oprName, $item, $this);
                }
            }
        }
        else {
            $closure();
        }
    }

    /**
     * @param CartItem $item
     * @throws Exception
     */
    public function addItem(CartItem $item)
    {
        $itemID = $item->getID();
        if ($this->contains($itemID)) {

            $closure = function() use ($itemID, $item) {
                $exist_item = $this->get($itemID);
                $exist_item->increment($item->getQuantity());
            };
            $this->emit($closure, ICartListener::ITEM_QTY_INCREMENT, $item);

        }
        else {

            $closure = function() use ($itemID, $item) {
                $this->items[$itemID] = $item;
            };
            $this->emit($closure, ICartListener::ITEM_ADD, $item);

        }
    }

    /**
     * @param int $piID
     * @throws Exception
     */
    public function increment(int $piID)
    {
        if ($this->contains($piID)) {
            $item = $this->get($piID);
            $closure = function() use ($item) {
                $item->increment();
            };
            $this->emit($closure, ICartListener::ITEM_QTY_INCREMENT, $item);


        }
    }

    /**
     * @param int $piID
     * @throws Exception
     */
    public function decrement(int $piID)
    {
        if ($this->contains($piID)) {

            $item = $this->get($piID);

            $closure = function() use ($item) {
                $item->decrement();
            };
            $this->emit($closure, ICartListener::ITEM_QTY_DECREMENT, $item, $this);

            if ($item->getQuantity()<1) {
                $this->removeItem($item);
            }

        }
    }

    /**
     * @param int $itemID
     * @return CartItem
     * @throws Exception
     */
    public function get(int $itemID): CartItem
    {
        if (isset($this->items[$itemID])) {
            return $this->items[$itemID];
        }
        throw new Exception("'$itemID' not found");
    }

    /**
     * @param int $itemID
     * @return bool
     */
    public function contains(int $itemID) : bool
    {
        return isset($this->items[$itemID]);
    }

    /**
     * @param int $itemID
     */
    public function remove(int $itemID)
    {
        if (isset($this->items[$itemID])) {

            $item = $this->items[$itemID];

            $closure = function() use ($item, $itemID) {
                unset($this->items[$itemID]);
            };

            $this->emit($closure, ICartListener::ITEM_REMOVE, $item);
        }
    }

    /**
     * @param CartItem $item
     */
    public function removeItem(CartItem $item)
    {
        $itemID = $item->getID();
        $this->remove($itemID);
    }

    /**
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function itemsCount(): int
    {
        $num_items = 0;
        foreach ($this->items as $itemID => $item) {
            if (!($item instanceof CartItem)) continue;
            $num_items += $item->getQuantity();
        }
        return $num_items;
    }

    /**
     *
     */
    public function clear()
    {
        $closure = function() {
            $this->items = array();
        };
        $this->emit($closure, ICartListener::CART_CLEAR, null);

    }

    /**
     * @return float
     */
    public function getItemsTotal(): float
    {
        $items_total = 0.0;
        foreach ($this->items as $itemID => $item) {
            if (!($item instanceof CartItem)) continue;
            $items_total += $item->getLineTotal();
        }
        return $items_total;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setData(string $name, string $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getData(string $name) : string
    {
        return $this->data[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function haveData(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    /**
     * @param string $text
     */
    public function setNote(string $text)
    {
        $this->note = mb_substr($text, 0, Cart::NOTE_MAX_LENGTH);
    }

    /**
     * @return string
     */
    public function getNote() : string
    {
        return $this->note;
    }

    /**
     * @param bool $mode
     */
    public function setRequireInvoice(bool $mode)
    {
        $this->require_invoice = $mode;
    }

    /**
     * @return bool
     */
    public function getRequireInvoice() : bool
    {
        return $this->require_invoice;
    }

    /**
     * @return Delivery
     */
    public function getDelivery() : Delivery
    {
        return $this->delivery;
    }

    /**
     * @return IDiscountProcessor
     */
    public function getDiscount() : IDiscountProcessor
    {
        return $this->discountProcessor;
    }

    /**
     * @param IDiscountProcessor $proc
     */
    public function setDiscount(IDiscountProcessor $proc) {
        $this->discountProcessor = $proc;
    }

}

?>