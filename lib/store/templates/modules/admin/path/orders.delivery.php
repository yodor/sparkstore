<?php
include_once("store/forms/DeliveryConfigForm.php");

$config = ConfigEditor::Configure("delivery_options", DeliveryConfigForm::class);
$config->title = tr("Delivery Options");
$config->clearNavigation = true;
