<?php
include_once("session.php");
include_once("components/templates/admin/ConfigEditorPage.php");

include_once("store/forms/ContactsConfigForm.php");

$cmp = new ConfigEditorPage();
$cmp->setConfigSection("global");
$cmp->setForm(new ContactsConfigForm());

$cmp->getPage()->navigation()->clear();
$cmp->render();