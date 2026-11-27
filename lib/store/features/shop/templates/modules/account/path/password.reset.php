<?php
include_once("store/features/shop/templates/modules/account/content/AccountPasswordReset.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountPasswordReset::class;
$config->requireAuth = false;
