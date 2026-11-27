<?php
include_once("store/features/shop/templates/modules/checkout/content/CheckoutGuest.php");
$config = TemplateConfig::Factory();
$config->contentClass = CheckoutGuest::class;
$config->requireAuth = false;
