<?php
include_once("session.php");
include_once("class/pages/AccountPage.php");
include_once("store/forms/RegisterClientInputForm.php");
include_once("store/forms/processors/RegisterClientFormProcessor.php");

$page = new AccountPage();

$form = new RegisterClientInputForm();
$form->removeInput("accept_terms");

$form->loadBeanData($page->getUserID(), new UsersBean());
$form->getInput("email")->setEditable(false);
$form->getInput("email")->getRenderer()->getAddonContainer()->items()->clear();
$form->getInput("password")->setValue("");
$form->getInput("password")->setRequired(false);

$frend = new FormRenderer($form);
$frend->setLayout(FormRenderer::FIELD_VBOX);
$frend->getSubmitButton()->setContents("Submit");

$proc = new RegisterClientFormProcessor();
$proc->setEditID($page->getUserID());

$proc->process($form);

if ($proc->getStatus() == FormProcessor::STATUS_OK) {
    Session::SetAlert(tr("Профилът беше променен успешно"));
    header("Location: profile.php");
    exit;
}
else if ($proc->getStatus() == FormProcessor::STATUS_ERROR) {
    Session::SetAlert($proc->getMessage());
    $form->loadBeanData($page->getUserID(), new UsersBean());
}

$page->startRender();

echo "<div class='column'>";
$page->setTitle(tr("Клиентски профил"));

echo "<h1 class='Caption'>" . $page->getTitle() . "</h1>";

echo "<div class='panel'>";
$frend->render();
echo "</div>";

//echo "<h1 class='Caption'>" . tr("Парола за достъп") . "</h1>";
//
//echo "<a class='ColorButton' href='generate_password.php'>";
//echo tr("Генерирай нова парола");
//echo "</a>";

echo "</div>";
$page->finishRender();
?>
