<?php
include_once("store/templates/modules/account/content/AccountPassword.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountPassword::class;
$config->requireAuth = false;
