<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/forms/processors/ClientAddressFormProcessor.php");


$page = new CheckoutPage();

$page->ensureCartItems();
$page->ensureClient();

$cab = new ClientAddressesBean();
$form = new ClientAddressInputForm();

$editID = -1;
$row = $cab->getResult("userID", $page->getUserID());
if ($row) {
    $editID = $row[$cab->key()];
    $form->loadBeanData($editID, $cab);
}

$proc = new ClientAddressFormProcessor();
$proc->setEditID($editID);
$proc->setUserID($page->getUserID());
$proc->setBean($cab);

$frend = new FormRenderer($form);
$frend->getSubmitLine()->setEnabled(FALSE);

$form->setProcessor($proc);

$proc->process($form);

if ($proc->getStatus() == FormProcessor::STATUS_OK) {
    Cart::Instance()->getDelivery()->getSelectedCourier()->setSelectedOption(DeliveryOption::USER_ADDRESS);
    header("Location: confirm.php");
    exit;
}
else if ($proc->getStatus() == FormProcessor::STATUS_ERROR) {
    Session::SetAlert($proc->getMessage());
}

$page->startRender();

$page->setTitle(tr("Адрес за доставка"));

$page->drawCartItems();

echo "<div class='delivery_details'>";

echo "<h1 class='Caption'>" . tr("Адрес за доставка") . "</h1>";

$frend->render();

$back_url = Session::get("checkout.navigation.back", $page->getPageURL());

$action = $page->getAction(CheckoutPage::NAV_LEFT);
$action->setTitle(tr("Назад"));
$action->setClassName("edit");
$action->getURL()->fromString($back_url);

$action = $page->getAction(CheckoutPage::NAV_RIGHT);
$action->setTitle(tr("Продължи"));
$action->setClassName("checkout");
$action->getURL()->fromString("javascript:document.forms.ClientAddressInputForm.submit();");


$page->renderNavigation();

echo "</div>"; //delivery_details

$page->finishRender();
?>
