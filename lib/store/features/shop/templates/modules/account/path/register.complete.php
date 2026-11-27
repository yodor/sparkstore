<?php
include_once("store/features/shop/templates/modules/account/content/AccountRegisterComplete.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountRegisterComplete::class;
$config->requireAuth = false;
