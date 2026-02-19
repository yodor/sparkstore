<?php

include_once("components/templates/admin/ConfigEditorPage.php");
include_once("store/forms/MarketingConfigForm.php");

$template = new ConfigEditorPage();
$template->setConfigSection("marketing_config");
$template->setForm(new MarketingConfigForm());

$template->getPage()->navigation()->clear();