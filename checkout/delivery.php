<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");
include_once("store/forms/DeliveryCourierForm.php");

class DeliveryCourierProcessor extends FormProcessor
{

    protected function processImpl(InputForm $form) : void
    {

        parent::processImpl($form);

        $cart = Cart::Instance();

        $delivery_courier = $form->getInput("delivery_courier")->getValue();

        $cart->getDelivery()->setSelectedCourier($delivery_courier[0]);
        $cart->store();
    }

}

$page = new CheckoutPage();

$page->ensureCartItems();
$page->ensureClient();

$cart = Cart::Instance();
//$cart->getDelivery()->setSelectedCourier(DeliveryCourier::NONE);
//$cart->store();

$form = new DeliveryCourierForm();
$form->setName("DeliveryCourier");

$proc = new DeliveryCourierProcessor();

$frend = new FormRenderer($form);


$proc->process($form);


$courier = $cart->getDelivery()->getSelectedCourier();


if ($proc->getStatus() == IFormProcessor::STATUS_NOT_PROCESSED) {

    if (!is_null($courier)) {

        $form->getInput("delivery_courier")->setValue($courier->getID());
    }
}
else if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {
    Session::set("alert", $proc->getMessage());

}
else if ($proc->getStatus() == IFormProcessor::STATUS_OK) {

    if (!is_null($courier)) {

        header("Location: delivery_option.php");
        exit;

    }

}

$frend->getSubmitLine()->setRenderEnabled(false);

$page->setTitle(tr("Избор на куриер"));

$page->base()->addClassName("delivery_courier");
$page->base()->items()->append($frend);

$page->initialize();

$back_url = Session::get("checkout.navigation.back", "cart.php");

$action = $page->getAction(CheckoutPage::NAV_LEFT);
$action->setContents(tr("Назад"));
$action->setClassName("edit");
$action->getURL()->fromString($back_url);

$action = $page->getAction(CheckoutPage::NAV_RIGHT);
$action->setContents(tr("Продължи"));
$action->setClassName("checkout");
$action->getURL()->fromString("javascript:document.forms.DeliveryCourier.submit();");

$page->render();

Session::set("checkout.navigation.back",  URL::Current()->toString());


?>
