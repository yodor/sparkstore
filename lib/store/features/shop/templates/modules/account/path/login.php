<?php
include_once("store/features/shop/templates/modules/account/content/AccountLogin.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountLogin::class;
$config->requireAuth = false;
