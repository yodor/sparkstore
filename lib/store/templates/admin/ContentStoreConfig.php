<?php
include_once("templates/admin/ConfigEditorPage.php");
include_once("store/forms/SparkStoreConfigForm.php");

$template = new ConfigEditorPage();
$template->setConfigSection("store_config");
$template->setForm(new SparkStoreConfigForm());

$template->getPage()->navigation()->clear();
?>