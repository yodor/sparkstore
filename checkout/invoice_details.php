<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");

include_once("store/forms/InvoiceDetailsInputForm.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/forms/processors/InvoiceDetailsFormProcessor.php");
include_once("db/BeanTransactor.php");

$page = new CheckoutPage();

$page->ensureCartItems();
$page->ensureClient();

$ccb = new InvoiceDetailsBean();
$form = new InvoiceDetailsInputForm();
$form->setName("InvoiceDetails");

$editID = -1;
$row = $ccb->getResult("userID", $page->getUserID());
if ($row) {
    $editID = $row[$ccb->key()];
    $form->loadBeanData($editID, $ccb);
}

$proc = new InvoiceDetailsFormProcessor();
$proc->setEditID($editID);
$proc->setUserID($page->getUserID());
$proc->setBean($ccb);

$frend = new FormRenderer($form);
$frend->setClassName("InvoiceDetails");
$frend->getSubmitLine()->setRenderEnabled(FALSE);

$proc->process($form);

if ($proc->getStatus() == FormProcessor::STATUS_OK) {
    header("Location: confirm.php");
    exit;
}
else if ($proc->getStatus() == FormProcessor::STATUS_ERROR) {
    Session::SetAlert($proc->getMessage());
}

$page->setTitle(tr("Детайли за фактуриране"));


$page->base()->addClassName("invoice_details");
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
$action->getURL()->fromString("javascript:document.forms.InvoiceDetails.submit()");

$page->render();
?>
