<?php
include_once("store/templates/modules/checkout/content/CheckoutContent.php");
include_once("components/LabelSpan.php");
include_once("components/TextComponent.php");

/**
 * After the order: thanks and the number of the order just made (session 'checkout.orderID' - set by finalize;
 * a number in the URL is not shown)
 */
class CheckoutComplete extends CheckoutContent
{
    public function initialize() : void
    {
        $page = $this->page();
        $page->getCartComponent()->setRenderEnabled(false);

        $tickMark = new Component(false);
        $tickMark->setComponentClass("tick_mark");
        $page->base()->items()->append($tickMark);

        $caption = new Component(false);
        $caption->setComponentClass("caption");
        $caption->setText(tr("Благодарим Ви че пазарувахте при нас!"));
        $page->base()->items()->append($caption);

        $orderID = (int)Session::Get("checkout.orderID", 0);
        if ($orderID > 0) {
            $orderNumber = new LabelSpan();
            $orderNumber->addClassName("order_number");
            $orderNumber->label()->setContents(tr("Номер на поръчката"));
            $orderNumber->span()->setContents((string)$orderID);
            $page->base()->items()->append($orderNumber);
        }

        $page->base()->items()->append(new TextComponent(tr("Ще се свържем с Вас относно детайли за Вашата поръчка")));
    }

    protected function getContentTitle() : string
    {
        return tr("Завършена поръчка");
    }
}
