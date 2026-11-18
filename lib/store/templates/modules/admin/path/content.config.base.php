<?php
include_once("store/forms/SparkStoreConfigForm.php");

$config = ConfigEditor::Configure("store_config", SparkStoreConfigForm::class);
$config->title = tr("Base Configuration");
