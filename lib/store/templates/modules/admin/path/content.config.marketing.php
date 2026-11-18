<?php
include_once("store/forms/MarketingConfigForm.php");
include_once("templates/content/ConfigEditor.php");

$config = ConfigEditor::Configure("marketing_config", MarketingConfigForm::class);
$config->title = tr("Marketing Configuration");
