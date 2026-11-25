<?php
include_once("store/forms/StoreMarketingConfigForm.php");
include_once("templates/content/ConfigEditor.php");

//the store form: the core tracking IDs and the conversions of the products (the last config is the active one)
$config = ConfigEditor::Configure(Marketing::SECTION, StoreMarketingConfigForm::class);
$config->title = tr("Marketing");
