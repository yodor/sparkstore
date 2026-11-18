<?php
include_once("store/templates/modules/checkout/content/CheckoutCart.php");
$config = TemplateConfig::Factory();
$config->contentClass = CheckoutCart::class;
$config->requireAuth = false;
