<?php
include_once("store/templates/modules/checkout/content/CheckoutGuest.php");
$config = TemplateConfig::Factory();
$config->contentClass = CheckoutGuest::class;
$config->requireAuth = false;
