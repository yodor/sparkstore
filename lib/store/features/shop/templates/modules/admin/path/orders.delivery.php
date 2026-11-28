<?php
include_once("store/features/shop/forms/DeliveryConfigForm.php");
include_once("templates/content/ConfigEditor.php");

$config = ConfigEditor::Configure("delivery_options", DeliveryConfigForm::class);
$config->title = tr("Delivery Options");
$config->clearNavigation = true;
