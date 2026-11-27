<?php
include_once("store/features/shop/templates/modules/account/content/AccountPassword.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountPassword::class;
$config->requireAuth = false;
