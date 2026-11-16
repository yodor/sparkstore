<?php
include_once("components/Container.php");
include_once("components/Action.php");
include_once("components/Button.php");

/**
 * One section of the checkout pages: caption, value and its 'change' button
 */
class OrderSection extends Container
{
    protected Container $value;
    protected Button $button;
    public function __construct(string $title, string $className)
    {
        parent::__construct(false);
        $this->setComponentClass("item");
        $this->addClassName($className);

        $this->setCaption($title);

        $this->value = new Container(false);
        $this->value->setComponentClass("value");
        $this->items()->append($this->value);

        $this->button = Button::Action( tr("Промени"), "", "ColorButton");
        $this->items()->append($this->button);
    }
    public function value() : Container
    {
        return $this->value;
    }
    public function button() : Button
    {
        return $this->button;
    }
}

/**
 * Navigation button of the checkout pages (left, center, right): an icon and the text
 */
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
