<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");
include_once("store/forms/DeliveryAddressForm.php");
include_once("store/beans/ClientAddressesBean.php");

class DeliveryAddressProcessor extends FormProcessor
{

    protected function processImpl(InputForm $form)
    {

        parent::processImpl($form);

        $cart = Cart::Instance();

        $delivery_option = $form->getInput("delivery_option")->getValue();

        $cart->getDelivery()->getSelectedCourier()->setSelectedOption($delivery_option[0]);
        $cart->store();
    }

}

$page = new CheckoutPage();

$page->ensureCartItems();
$page->ensureClient();

$cart = Cart::Instance();
$courier = $cart->getDelivery()->getSelectedCourier();
if (is_null($courier)) {
    header("Location: delivery.php");
    exit;
}
//$courier->setSelectedOption(DeliveryOption::NONE);
//$cart->store();

$form = new DeliveryAddressForm();
$form->setName("DeliveryAddress");

$proc = new DeliveryAddressProcessor();

$frend = new FormRenderer($form);

$bean = new ClientAddressesBean();



$proc->process($form);


$option = $cart->getDelivery()->getSelectedCourier()->getSelectedOption();


if ($proc->getStatus() == FormProcessor::STATUS_NOT_PROCESSED) {

    if (!is_null($option)) {

        $form->getInput("delivery_option")->setValue($option->getID());
    }
}
else if ($proc->getStatus() == FormProcessor::STATUS_ERROR) {
    Session::set("alert", $proc->getMessage());

}
else if ($proc->getStatus() == FormProcessor::STATUS_OK) {

    if (!is_null($option)) {

        if ($option->getID() == DeliveryOption::USER_ADDRESS) {

            $cabrow = $bean->getResult("userID", $page->getUserID());
            if (!$cabrow) {
                header("Location: delivery_address.php");
                exit;
            }
            else {
                header("Location: confirm.php");
                exit;
            }

        }
        else if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
            header("Location: delivery_courier.php");
            exit;
        }

    }

}

$page->startRender();
$page->setTitle(tr("Начин на доставка"));

// echo "UserID: ".$page->getUserID();

$page->drawCartItems();

// $page->showShippingInfo();

echo "<div class='delivery_address'>";

echo "<h1 class='Caption'>" . $page->getTitle() . "</h1>";

$frend->startRender();
$frend->renderInputs();
$frend->renderSubmitValue();
$frend->finishRender();

echo "</div>";

$back_url = Session::get("checkout.navigation.back", "cart.php");

$action = $page->getAction(CheckoutPage::NAV_LEFT);
$action->setTitle(tr("Назад"));
$action->setClassName("edit");
$action->getURLBuilder()->buildFrom($back_url);

$action = $page->getAction(CheckoutPage::NAV_RIGHT);
$action->setTitle(tr("Продължи"));
$action->setClassName("checkout");
$action->getURLBuilder()->buildFrom("javascript:document.forms.DeliveryAddress.submit();");

$page->renderNavigation();

$page->finishRender();
?>
