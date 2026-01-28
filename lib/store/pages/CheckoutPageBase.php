<?php
include_once("class/pages/StorePage.php");

include_once("store/components/CartComponent.php");
include_once("components/ClosureComponent.php");
include_once("components/Action.php");

class NavButton extends Action
{

    public function __construct()
    {
        parent::__construct();
        $this->setComponentClass("checkout_button");
        $icon = new Component(false);
        $icon->setTagName("span");
        $icon->setComponentClass("icon");
        $this->items()->append($icon);
        $this->items_first = true;
//        $this->items()->append($button);
//        echo "<div class='ColorButton checkout_button' >" . tr($action->getTitle()) . "</div>";
    }

}
class CheckoutPageBase extends StorePage
{

    protected CartComponent $ccmp;

    const string NAV_LEFT = "left";
    const string NAV_CENTER = "center";
    const string NAV_RIGHT = "right";

    protected Container $navigation;
    protected Component $heading;
    protected Container $content;

    public function __construct()
    {
        parent::__construct();

        $this->head()->addCSS(STORE_LOCAL . "/css/checkout.css");

        $this->heading = new Component(false);
        $this->heading->setTagName("h1");
        $this->heading->setComponentClass("Caption");
        $this->items()->append($this->heading);

        $this->ccmp = new CartComponent();
        $this->items()->append($this->ccmp);

        $this->content = new Container(false);
        $this->content->setComponentClass("base");
        $this->items()->append($this->content);

        $this->navigation = new Container();
        $this->navigation->setClassName("navigation");
        $this->items()->append($this->navigation);



        $navLeft = new NavButton();
        $navLeft->setName(CheckoutPageBase::NAV_LEFT);
        $navLeft->setClassName("disabled");
        $this->navigation->items()->append($navLeft);

        $navCenter = new NavButton();
        $navCenter->setName(CheckoutPageBase::NAV_CENTER);
        $navCenter->setClassName("disabled");
        $this->navigation->items()->append($navCenter);

        $navRight = new NavButton();
        $navRight->setName(CheckoutPageBase::NAV_RIGHT);
        $navRight->setClassName("disabled");
        $this->navigation->items()->append($navRight);


    }

    protected function applyTitleDescription(): void
    {
        parent::applyTitleDescription();
        $this->heading->setContents($this->getTitle());
    }

    public function initialize() : void
    {
        $this->ccmp->initialize();
    }

    public function getCartComponent() : CartComponent
    {
        return $this->ccmp;
    }

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

    public function ensureCartItems() : void
    {
        if (Cart::Instance()->itemsCount() < 1) {
            Session::SetAlert(tr("Вашата кошница е празна"));
            header("Location: cart.php");
            exit;
        }
    }

    public function ensureClient() : void
    {
        if (!$this->context) {
            Session::SetAlert(tr("Изисква регистрация"));
            header("Location: cart.php");
            exit;
        }
    }

    public static function OrderProcessor() : OrderProcessor
    {
        include_once("store/utils/OrderProcessor.php");
        return new OrderProcessor();
    }

}

?>
