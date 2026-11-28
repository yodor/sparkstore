<?php
include_once("templates/TemplateContent.php");
include_once("store/utils/cart/Cart.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/courier/CourierOffices.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/features/shop/components/CartComponent.php");
include_once("store/features/shop/components/CheckoutSections.php");

/**
 * Content of the checkout module - the layout of the checkout pages in the store frame: the heading (the page
 * title), the cart, the navigation buttons (left, center, right) and the 'base' container. The module look
 * (body class CheckoutPage, checkout.css) is set by the checkout init.php.
 */
abstract class CheckoutContent extends TemplateContent
{
    const string NAV_LEFT = "left";
    const string NAV_CENTER = "center";
    const string NAV_RIGHT = "right";

    protected Component $heading;
    protected CartComponent $ccmp;
    protected Container $navigationBar;
    protected Container $base;

    public function __construct()
    {
        parent::__construct();
        $this->cmp = new Container(false);

        $this->heading = new Component(false);
        $this->heading->setTagName("h1");
        $this->heading->setComponentClass("Caption");
        $this->cmp->items()->append($this->heading);

        $this->ccmp = new CartComponent();
        $this->cmp->items()->append($this->ccmp);

        $this->navigationBar = new Container();
        $this->navigationBar->setClassName("navigation");
        $this->cmp->items()->append($this->navigationBar);

        $this->base = new Container(false);
        $this->base->setComponentClass("base");
        $this->cmp->items()->append($this->base);

        foreach (array(CheckoutContent::NAV_LEFT, CheckoutContent::NAV_CENTER, CheckoutContent::NAV_RIGHT) as $name) {
            $button = new NavButton();
            $button->setName($name);
            $button->setClassName("disabled");
            $this->navigationBar->items()->append($button);
        }
    }

    /**
     * The checkout layout parts of this content (the cart, base, navigation buttons)
     * @return CheckoutContent
     */
    protected function page() : CheckoutContent
    {
        return $this;
    }

    public function getCartComponent() : CartComponent
    {
        return $this->ccmp;
    }

    /**
     * Navigation button NAV_LEFT, NAV_CENTER or NAV_RIGHT
     * @param string $name
     * @return Action
     * @throws Exception
     */
    public function getAction(string $name) : Action
    {
        $cmp = $this->navigationBar->items()->getByName($name);
        if ($cmp instanceof Action) {
            return $cmp;
        }
        throw new Exception("Action '$name' does not exist");
    }

    public function base() : Container
    {
        return $this->base;
    }

    public function getNavigation() : Container
    {
        return $this->navigationBar;
    }

    /**
     * The heading is the final page title
     * @param SparkPage $page
     * @return void
     */
    public function finalizeHead(SparkPage $page) : void
    {
        $this->heading->setText($page->getTitle());
    }

    /**
     * The order processor of finalize - a site can return its own (a subclass of this content for finalize)
     * @return OrderProcessor
     */
    public static function OrderProcessor() : OrderProcessor
    {
        include_once("store/utils/OrderProcessor.php");
        return new OrderProcessor();
    }

    public function component() : Container
    {
        return $this->cmp;
    }

    /**
     * Redirect to the module path '$path' and stop
     * @param string $path
     * @param string $alert Session alert to show there
     * @return never
     */
    protected function redirect(string $path, string $alert = "") : never
    {
        if ($alert !== "") Session::SetAlert($alert);
        header("Location: " . Module::PathURL($path));
        exit;
    }

    /**
     * An empty cart goes to the cart page
     * @return void
     */
    protected function ensureCartItems() : void
    {
        if (Cart::Instance()->itemsCount() < 1) {
            $this->redirect("cart", tr("Your cart is empty"));
        }
    }

    /**
     * The first step of the order data that is still missing (module path), "" when all is there:
     * courier → delivery option (door / office) → the registered address (door) or the courier office (office)
     * → the invoice details (when an invoice is required). confirm and finalize send the client there.
     * @param int $userID
     * @return string
     * @throws Exception
     */
    public static function MissingStep(int $userID) : string
    {
        $cart = Cart::Instance();
        $courier = $cart->getDelivery()->getSelectedCourier();
        if (is_null($courier)) return "delivery";

        $option = $courier->getSelectedOption();
        if (is_null($option)) return "delivery/option";

        if ($option->getID() == DeliveryOption::USER_ADDRESS) {
            if (!CheckoutContent::HaveRow(new ClientAddressesBean(), $userID)) return "delivery/address";
        }
        else if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
            //an office of the list of the courier (CourierOffices) - a typed or closed one is chosen again
            if (is_null(CourierOffices::Chosen($userID, $courier->getID()))) return "delivery/courier";
        }

        if ($cart->getRequireInvoice() && !CheckoutContent::HaveRow(new InvoiceDetailsBean(), $userID)) return "invoice";

        return "";
    }

    /**
     * The client has a row in '$bean' (column userID)
     * @param DBTableBean $bean
     * @param int $userID
     * @return bool
     * @throws Exception
     */
    public static function HaveRow(DBTableBean $bean, int $userID) : bool
    {
        return is_array($bean->getResult("userID", (string)$userID, $bean->key()));
    }

    /**
     * After a step is saved: its next step, or confirm when the next one is already filled
     * @param string $next module path of the next step
     * @return never
     */
    protected function nextStep(string $next) : never
    {
        $missing = CheckoutContent::MissingStep(Module::Active()->getUserID());
        $this->redirect((strcmp($missing, $next) === 0) ? $next : "confirm");
    }

    /**
     * 'Back' of the steps: the cart or confirm (the last of them visited)
     * @return string
     */
    protected function backURL() : string
    {
        return (string)Session::Get("checkout.navigation.back", Module::PathURL("cart")->toString());
    }

    /**
     * Navigation button: text, css class and target (a module path or javascript:)
     * @param string $name CheckoutContent::NAV_LEFT / NAV_CENTER / NAV_RIGHT
     * @param string $text
     * @param string $className
     * @param string $href
     * @return Action
     */
    protected function navigation(string $name, string $text, string $className, string $href) : Action
    {
        $action = $this->page()->getAction($name);
        $action->setContents($text);
        $action->setClassName($className);
        $action->getURL()->fromString($href);
        return $action;
    }
}
