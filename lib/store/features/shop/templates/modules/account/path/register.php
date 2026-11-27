<?php
include_once("store/features/shop/templates/modules/account/content/AccountRegister.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountRegister::class;
$config->requireAuth = false;
