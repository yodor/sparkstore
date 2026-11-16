<?php
include_once("store/templates/content/CheckoutRowEditor.php");

/**
 * Step 3, door delivery: the registered address of the client (ClientAddressesBean)
 */
class CheckoutDeliveryAddress extends CheckoutRowEditor
{
    protected string $baseClass = "delivery_details";

    protected function check() : void
    {
        if (is_null(Cart::Instance()->getDelivery()->getSelectedCourier())) $this->redirect("delivery");
    }

    protected function saved() : void
    {
        $cart = Cart::Instance();
        $cart->getDelivery()->getSelectedCourier()?->setSelectedOption(DeliveryOption::USER_ADDRESS);
        $cart->store();
    }
}
