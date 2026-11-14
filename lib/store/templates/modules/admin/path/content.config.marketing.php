<?php
include_once("store/forms/MarketingConfigForm.php");

$config = TemplateConfig::Config("marketing_config", MarketingConfigForm::class);
$config->title = tr("Marketing Configuration");
