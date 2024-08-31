<?php
include_once("beans/ConfigBean.php");
include_once("store/utils/cart/CartEntry.php");
include_once("store/utils/cart/ZeroDiscount.php");
include_once("store/utils/cart/Delivery.php");
include_once("store/utils/cart/ICartListener.php");

class Cart
{
    /**
     * @var array of CartEntry
     */
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

    const VERSION = "2.0";

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
                debug("de-serialize success - calling delivery option initialization");
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
        self::$session_key = sparkHash(Cart::SESSION_KEY . "-" . Cart::VERSION . "-" . SITE_TITLE);
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
     * @param ?CartEntry $item
     */
    protected function emit(Closure $closure, string $oprName, ?CartEntry $item)
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
     * @param CartEntry $item
     * @throws Exception
     */
    public function addItem(CartEntry $item)
    {
        $itemHash = $item->getItem()->hash();
        debug("addItem with hash: $itemHash");

        if ($this->contains($itemHash)) {

            debug("Already existing item incrementing count");
            $closure = function() use ($itemHash, $item) {
                $exist_item = $this->get($itemHash);
                $exist_item->increment($item->getQuantity());
            };
            $this->emit($closure, ICartListener::ITEM_QTY_INCREMENT, $item);

        }
        else {
            debug("Non existing item setting new value");

            $closure = function() use ($itemHash, $item) {
                $this->items[$itemHash] = $item;
            };
            $this->emit($closure, ICartListener::ITEM_ADD, $item);

        }
    }

    /**
     * @param int $piID
     * @throws Exception
     */
    public function increment(string $itemHash)
    {
        if ($this->contains($itemHash)) {
            $item = $this->get($itemHash);
            $closure = function() use ($item) {
                $item->increment();
            };
            $this->emit($closure, ICartListener::ITEM_QTY_INCREMENT, $item);


        }
    }

    /**
     * @param string CartEntry hash
     * @throws Exception
     */
    public function decrement(string $itemHash)
    {
        if ($this->contains($itemHash)) {

            $item = $this->get($itemHash);

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
     * @param string CartEntry hash
     * @return CartEntry
     * @throws Exception
     */
    public function get(string $itemHash): CartEntry
    {
        if (isset($this->items[$itemHash])) {
            return $this->items[$itemHash];
        }
        throw new Exception("'$itemHash' not found");
    }

    /**
     * @param int $itemID
     * @return bool
     */
    public function contains(string $itemHash) : bool
    {
        return isset($this->items[$itemHash]);
    }

    /**
     * @param int $itemID
     */
    public function remove(string $itemHash)
    {
        if (isset($this->items[$itemHash])) {

            $item = $this->items[$itemHash];

            $closure = function() use ($item, $itemHash) {
                unset($this->items[$itemHash]);
            };

            $this->emit($closure, ICartListener::ITEM_REMOVE, $item);
        }
    }

    /**
     * @param CartEntry $item
     */
    public function removeItem(CartEntry $item)
    {
        $itemHash = $item->getItem()->hash();
        $this->remove($itemHash);
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
        foreach ($this->items as $itemHash => $item) {
            if (!($item instanceof CartEntry)) continue;
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
        foreach ($this->items as $itemHash => $item) {
            if (!($item instanceof CartEntry)) continue;
            $items_total += $item->getLineTotal();
        }
        return $items_total;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setData(string $name, string $value) : void
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
