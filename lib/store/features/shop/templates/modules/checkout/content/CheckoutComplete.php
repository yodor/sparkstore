<?php
include_once("store/features/shop/templates/modules/checkout/content/CheckoutContent.php");
include_once("components/LabelSpan.php");
include_once("components/TextComponent.php");
include_once("store/objects/data/GTMPurchaseEvent.php");
include_once("pages/SparkPage.php");

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
        $caption->setText(tr("Thank you for shopping with us!"));
        $page->base()->items()->append($caption);

        $orderID = (int)Session::Get("checkout.orderID", 0);
        if ($orderID > 0) {
            $orderNumber = new LabelSpan();
            $orderNumber->addClassName("order_number");
            $orderNumber->label()->setContents(tr("Order number"));
            $orderNumber->span()->setContents((string)$orderID);
            $page->base()->items()->append($orderNumber);

            //GA4 'purchase' once per order (a reload of the page does not count it again)
            if ((int)Session::Get("checkout.purchaseSent", 0) !== $orderID) {
                Session::Set("checkout.purchaseSent", $orderID);
                SparkPage::Instance()?->head()->addScript(new GTMPurchaseEvent($orderID));
            }
        }

        $page->base()->items()->append(new TextComponent(tr("We will contact you about the details of your order")));
    }

    protected function getContentTitle() : string
    {
        return tr("Order complete");
    }
}
