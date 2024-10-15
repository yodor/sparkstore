<?php
include_once("session.php");
include_once("templates/admin/ConfigEditorPage.php");
include_once("store/forms/DeliveryConfigForm.php");

include_once("beans/ConfigBean.php");
include_once("forms/processors/ConfigFormProcessor.php");
include_once("forms/renderers/FormRenderer.php");

$cmp = new ConfigEditorPage();
$cmp->setConfigSection("delivery_options");

$form = new DeliveryConfigForm();
$cmp->setForm($form);

$cmp->getPage()->navigation()->clear();

$cmp->render();
?>
