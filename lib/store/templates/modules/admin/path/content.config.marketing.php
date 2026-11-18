<?php
include_once("store/forms/MarketingConfigForm.php");

$config = ConfigEditor::Configure("marketing_config", MarketingConfigForm::class);
$config->title = tr("Marketing Configuration");
