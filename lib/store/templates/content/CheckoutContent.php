<?php
include_once("templates/TemplateContent.php");
include_once("store/utils/cart/Cart.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/beans/CourierAddressesBean.php");
include_once("store/beans/InvoiceDetailsBean.php");

/**
 * Content of the checkout module. Works on the page (CheckoutTemplatePage): its title, cart, navigation buttons
 * and 'base' container - the layout of the checkout pages.
 */
abstract class CheckoutContent extends TemplateContent
{
    public function __construct()
    {
        parent::__construct();
        $this->cmp = new Container(false);
    }

    protected function page() : CheckoutTemplatePage
    {
        $page = SparkPage::Instance();
        if (!($page instanceof CheckoutTemplatePage)) throw new Exception("Incorrect page - expecting CheckoutTemplatePage");
        return $page;
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
            $this->redirect("cart", tr("Вашата кошница е празна"));
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
            if (!CheckoutContent::HaveRow(new CourierAddressesBean(), $userID)) return "delivery/courier";
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
     * @param string $name CheckoutTemplatePage::NAV_LEFT / NAV_CENTER / NAV_RIGHT
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
