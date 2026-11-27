<?php
include_once("store/features/shop/templates/modules/checkout/content/CheckoutCart.php");
$config = TemplateConfig::Factory();
$config->contentClass = CheckoutCart::class;
$config->requireAuth = false;
