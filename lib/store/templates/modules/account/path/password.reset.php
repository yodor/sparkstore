<?php
include_once("store/templates/modules/account/content/AccountPasswordReset.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountPasswordReset::class;
$config->requireAuth = false;
