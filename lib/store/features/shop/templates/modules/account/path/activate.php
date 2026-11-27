<?php
include_once("store/features/shop/templates/modules/account/content/AccountActivate.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountActivate::class;
$config->requireAuth = false;
