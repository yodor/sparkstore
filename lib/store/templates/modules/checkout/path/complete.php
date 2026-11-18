<?php
include_once("store/templates/modules/checkout/content/CheckoutComplete.php");
$config = TemplateConfig::Factory();
$config->contentClass = CheckoutComplete::class;
$config->requireAuth = false;
