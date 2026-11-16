<?php
include_once("templates/TemplateContent.php");
include_once("store/utils/cart/Cart.php");

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
