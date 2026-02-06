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
    protected array $items = array();

    protected string $note = "";

    protected bool $require_invoice = FALSE;

    protected array $data = array();

    protected array $cartListeners = array();

    protected $discountProcessor = NULL;
    protected $delivery = NULL;
    /**
     * @var null|Cart
     */
    protected static ?Cart $instance = NULL;
    protected static ?string $session_key = NULL;

    const SESSION_KEY = "spark_cart";

    const VERSION = "2.0";

    const NOTE_MAX_LENGTH = 255;

    static public function Instance(): Cart
    {
        if (self::$instance instanceof Cart) {
            Debug::ErrorLog("Returning already assigned instance");
            return self::$instance;
        }

        $cart = NULL;
        try {
            if (Session::Contains(Cart::SessionKey())) {
                Debug::ErrorLog("Trying to de-serialize Cart object from session");
                $cart = @unserialize(Session::Get(Cart::SessionKey()));

                if ($cart instanceof Cart) {
                    Debug::ErrorLog("de-serialize success - calling delivery option initialization");
                    //check correctnes of keys in cart
                    $cart->check();
                    $cart->getDelivery()->initialize();
                    $cart->store();
                } else {
                    $cart = NULL;
                }
            }
        }
        catch (Exception $e) {
            Debug::ErrorLog("de-serialize Cart error: " . $e->getMessage());
            $cart = null;
        }

        if (is_null($cart)) {
            Debug::ErrorLog("Creating new instance of Cart");
            $cart = new Cart();
            $cart->store();
        }



        self::$instance = $cart;

        Debug::ErrorLog("Returning cart instance");

        return self::$instance;
    }

    static public function SessionKey(): string
    {
        if (self::$session_key) {
            return self::$session_key;
        }
        self::$session_key = Spark::Hash(Cart::SESSION_KEY . "-" . Cart::VERSION . "-" . Spark::Get(Config::SITE_TITLE));
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

    protected function check()
    {
        foreach ($this->items as $itemHash => $cartEntry) {
            $currentHash = $cartEntry->getItem()->hash();

            if ($itemHash != $currentHash) {
                unset($this->items[$itemHash]);
            }
        }
    }
    /**
     * Add cart listener
     * @param ICartListener $listener
     * @return void
     */
    public function addCartListener(ICartListener $listener) : void
    {
        $this->cartListeners[] = $listener;
    }

    /**
     * Serialize cart to session
     * @return void
     */
    public function store() : void
    {
        Session::Set(Cart::SessionKey(), serialize($this));
    }

    /**
     * Notify all cartListeners about cart changes
     * @param Closure $closure
     * @param string $oprName
     * @param ?CartEntry $item
     */
    protected function emit(Closure $closure, string $oprName, ?CartEntry $item) : void
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
     * Add item to cart
     * @param CartEntry $item
     * @throws Exception
     */
    public function addItem(CartEntry $item) : void
    {
        $itemHash = $item->getItem()->hash();

        if ($this->contains($itemHash)) {
            Debug::ErrorLog("Already existing item incrementing count");
            $closure = function() use ($itemHash, $item) {
                $exist_item = $this->get($itemHash);
                $exist_item->increment($item->getQuantity());
            };
            $this->emit($closure, ICartListener::ITEM_QTY_INCREMENT, $item);
        }
        else {
            Debug::ErrorLog("Non existing item setting new value");

            $closure = function() use ($itemHash, $item) {
                $this->items[$itemHash] = $item;
            };
            $this->emit($closure, ICartListener::ITEM_ADD, $item);
        }
    }


    /**
     * Increase cart item amount by 1
     * @param string $itemHash
     * @return void
     * @throws Exception
     */
    public function increment(string $itemHash) : void
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
     * Decrease cart item amount by 1
     * @param string $itemHash
     * @return void
     * @throws Exception
     */
    public function decrement(string $itemHash) : void
    {
        if ($this->contains($itemHash)) {

            $item = $this->get($itemHash);

            $closure = function() use ($item) {
                $item->decrement();
            };
            $this->emit($closure, ICartListener::ITEM_QTY_DECREMENT, $item);

            if ($item->getQuantity()<1) {
                $this->removeItem($item);
            }

        }
    }

    /**
     * Get item
     * @param string $itemHash
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
     * Check if cart contains item
     * @param string $itemHash
     * @return bool
     */
    public function contains(string $itemHash) : bool
    {
        return isset($this->items[$itemHash]);
    }

    /**
     * Remove item from cart
     * @param string $itemHash
     * @return void
     */
    public function remove(string $itemHash) : void
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
     * Remove item from cart
     * @param CartEntry $item
     * @return void
     */
    public function removeItem(CartEntry $item) : void
    {
        $itemHash = $item->getItem()->hash();
        $this->remove($itemHash);
    }


    /**
     * Return all items currently in the cart
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Number of all item quantities in the cart
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
     * Remove all items from the cart
     * @return void
     */
    public function clear() : void
    {
        $closure = function() {
            $this->items = array();
        };
        $this->emit($closure, ICartListener::CART_CLEAR, null);

    }

    /**
     * Return total amount for all items
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
     * Set data value
     * @param string $name
     * @param string $value
     */
    public function setData(string $name, string $value) : void
    {
        $this->data[$name] = $value;
    }

    /**
     * Get data value
     * @param string $name
     * @return string
     */
    public function getData(string $name) : string
    {
        return $this->data[$name];
    }

    /**
     * Check if data value is set
     * @param string $name
     * @return bool
     */
    public function haveData(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Set not text
     * @param string $text
     */
    public function setNote(string $text) : void
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
    public function setRequireInvoice(bool $mode) : void
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
    public function setDiscount(IDiscountProcessor $proc) : void
    {
        $this->discountProcessor = $proc;
    }

}

?>
