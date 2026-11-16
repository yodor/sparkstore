<?php
include_once("store/pages/StoreTemplatePage.php");
include_once("store/components/CartComponent.php");
include_once("store/components/CheckoutSections.php");
include_once("store/templates/content/CheckoutContent.php");
include_once("store/utils/cart/Cart.php");

/**
 * Page of the checkout module (StoreTemplatePage: the storefront look with the module path content).
 *
 * The layout of the checkout pages (CheckoutPageBase): the heading (page title), the cart, the navigation
 * buttons (left, center, right) and the 'base' container. CheckoutContent fills them through this page;
 * the component of any other content (error) goes into 'base'.
 * Body classes: 'CheckoutPage' (checkout.css) and the path ('delivery/option' → 'delivery_option').
 */
class CheckoutTemplatePage extends StoreTemplatePage
{
    const string NAV_LEFT = "left";
    const string NAV_CENTER = "center";
    const string NAV_RIGHT = "right";

    protected CartComponent $ccmp;

    protected Container $navigation;
    protected Component $heading;
    protected Container $content;

    public function __construct()
    {
        parent::__construct();

        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/checkout.css");
        $this->body()->addClassName("CheckoutPage");

        $this->heading = new Component(false);
        $this->heading->setTagName("h1");
        $this->heading->setComponentClass("Caption");
        $this->items()->append($this->heading);

        $this->ccmp = new CartComponent();
        $this->items()->append($this->ccmp);

        $this->navigation = new Container();
        $this->navigation->setClassName("navigation");
        $this->items()->append($this->navigation);

        $this->content = new Container(false);
        $this->content->setComponentClass("base");
        $this->items()->append($this->content);

        foreach (array(CheckoutTemplatePage::NAV_LEFT, CheckoutTemplatePage::NAV_CENTER, CheckoutTemplatePage::NAV_RIGHT) as $name) {
            $button = new NavButton();
            $button->setName($name);
            $button->setClassName("disabled");
            $this->navigation->items()->append($button);
        }
    }

    protected function applyTitleDescription(): void
    {
        parent::applyTitleDescription();
        $this->heading->setText($this->getTitle());
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
        $cmp = $this->navigation->items()->getByName($name);
        if ($cmp instanceof Action) {
            return $cmp;
        }
        throw new Exception("Action '$name' does not exist");
    }

    public function base() : Container
    {
        return $this->content;
    }

    public function getNavigation() : Container
    {
        return $this->navigation;
    }

    public function update(TemplateContent $content) : void
    {
        $config = $content->config();
        if ($config->title) $this->setTitle($config->title);

        //CheckoutContent works on the page itself
        if ($content instanceof CheckoutContent) return;

        if ($config->description) {
            $content->component()->setCaption($config->description);
        }
        $this->ccmp->setRenderEnabled(false);
        $this->content->items()->append($content->component());
    }
}
