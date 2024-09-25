<?php
include_once("session.php");
include_once("class/pages/AccountPage.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/forms/InvoiceDetailsInputForm.php");
include_once("store/forms/processors/InvoiceDetailsFormProcessor.php");

$page = new AccountPage();
$page->setTitle(tr("Детайли за фактуриране"));

$ccb = new InvoiceDetailsBean();
$form = new InvoiceDetailsInputForm();

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
$frend->setName("InvoiceDetails");

$form->setProcessor($proc);

$proc->process($form);

if ($proc->getStatus() == FormProcessor::STATUS_OK) {
    Session::SetAlert(tr("Детайлите за фактуриране бяха променени успешно"));
    header("Location: invoice_details.php");
    exit;
}
else if ($proc->getStatus() == FormProcessor::STATUS_ERROR) {
    Session::SetAlert($proc->getMessage());
}

$page->startRender();

echo "<div class='column'>";

echo "<h1 class='Caption'>" . $page->getTitle() . "</h1>";

echo "<div class='panel'>";
$frend->render();
echo "</div>";

echo "</div>"; //column

$page->finishRender();
?>
