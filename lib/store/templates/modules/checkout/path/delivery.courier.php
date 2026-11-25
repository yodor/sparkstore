<?php
include_once("store/templates/modules/checkout/content/CheckoutDeliveryCourier.php");

$config = TemplateConfig::Factory();
$config->contentClass = CheckoutDeliveryCourier::class;
$config->title = tr("Choose a courier office for the delivery");
