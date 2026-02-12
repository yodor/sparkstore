<?php
include_once("session.php");
include_once("class/pages/AccountPage.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/forms/processors/ClientAddressFormProcessor.php");

$page = new AccountPage();
$page->setTitle(tr("Регистриран адрес"));

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
$frend->setName("ClientAddress");

$form->setProcessor($proc);

$proc->process($form);

if ($proc->getStatus() == IFormProcessor::STATUS_OK) {
    Session::SetAlert(tr("Вашият адрес беше успешно променен"));
    header("Location: registered_address.php");
    exit;
}
else if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {
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