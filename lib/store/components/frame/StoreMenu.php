<?php
include_once("store/components/frame/StoreSection.php");
include_once("components/Action.php");
include_once("components/MenuBar.php");
include_once("components/KeywordSearch.php");
include_once("store/utils/cart/Cart.php");

/**
 * The menu of the store frame: the main menu (the menu bar of the frame) with the groups - the keyword search
 * and the customer buttons (account, cart)
 */
class StoreMenu extends StoreSection
{
    protected bool $searchGroup = true;
    protected bool $customerGroup = true;
    protected string $searchPlaceholder = "";

    public function __construct()
    {
        parent::__construct();
        $this->addClassName("menu");
    }

    /**
     * Show the keyword search in the menu (default: yes)
     * @param bool $enabled
     * @return $this
     */
    public function setSearchGroup(bool $enabled) : static
    {
        $this->searchGroup = $enabled;
        return $this;
    }

    /**
     * Show the customer buttons - account, cart (default: yes)
     * @param bool $enabled
     * @return $this
     */
    public function setCustomerGroup(bool $enabled) : static
    {
        $this->customerGroup = $enabled;
        return $this;
    }

    /**
     * The placeholder of the keyword search (default: 'Търси ...')
     * @param string $text
     * @return $this
     */
    public function setSearchPlaceholder(string $text) : static
    {
        $this->searchPlaceholder = $text;
        return $this;
    }

    public function fill(Container $content, StorePageBase $page) : void
    {
        $menuBar = $page->getMenuBar();
        $content->items()->append($menuBar);

        if ($this->searchPlaceholder !== "") {
            $page->getKeywordSearch()?->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("placeholder", $this->searchPlaceholder);
        }

        $groups = new Container(false);
        $groups->setComponentClass("menu_groups");
        if ($this->searchGroup && $page->getKeywordSearch() instanceof KeywordSearch) {
            $groups->items()->append(StoreMenu::SearchGroup($page->getKeywordSearch()));
        }
        if ($this->customerGroup) {
            $groups->items()->append(StoreMenu::CustomerGroup($page->getClient() instanceof AuthContext));
        }
        $menuBar->items()->append($groups);
    }

    /**
     * The search group of the menu
     * @param KeywordSearch $search
     * @return Container
     */
    public static function SearchGroup(KeywordSearch $search) : Container
    {
        $container = new Container(false);
        $container->setComponentClass("group search_pane");
        $container->items()->append($search);
        return $container;
    }

    /**
     * The customer group of the menu: the account button (logged or not), the cart button with its item count
     * @param bool $logged
     * @return Container
     */
    public static function CustomerGroup(bool $logged) : Container
    {
        $container = new Container(false);
        $container->setComponentClass("group customer_pane");

        $icon_contents = "<span class='icon'></span>";

        $button_account = new Action();
        $button_account->getURL()->fromString(Spark::Get(Config::LOCAL) . "/account/login");
        $button_account->setAttribute("title", tr("Account"));
        $button_account->setClassName("button account");
        $button_account->setContents($icon_contents);
        if ($logged) {
            $button_account->getURL()->fromString(Spark::Get(Config::LOCAL) . "/account/");
            $button_account->addClassName("logged");
        }
        $container->items()->append($button_account);

        $button_cart = new Action();
        $button_cart->getURL()->fromString(Spark::Get(Config::LOCAL) . "/checkout/cart");
        $button_cart->setAttribute("title", tr("Cart"));
        $button_cart->addClassName("button cart");

        $button_contents = $icon_contents;
        $cart_items = Cart::Instance()->itemsCount();
        if ($cart_items > 0) {
            $button_cart->setAttribute("item_count", $cart_items);
            $button_contents .= "<span class='items_dot'>$cart_items</span>";
        }
        $button_cart->setContents($button_contents);
        $container->items()->append($button_cart);

        return $container;
    }
}
