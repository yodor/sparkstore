<?php
include_once("store/forms/SparkStoreConfigForm.php");

$config = TemplateConfig::Config("store_config", SparkStoreConfigForm::class);
$config->title = tr("Base Configuration");
