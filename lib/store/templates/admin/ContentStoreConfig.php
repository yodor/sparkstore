<?php
include_once("templates/admin/ConfigEditorPage.php");
include_once("store/forms/StoreConfigForm.php");

$template = new ConfigEditorPage();
$template->setConfigSection("store_config");
$template->setForm(new StoreConfigForm());

$template->getPage()->navigation()->clear();
?>