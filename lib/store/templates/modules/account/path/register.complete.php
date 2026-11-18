<?php
include_once("store/templates/modules/account/content/AccountRegisterComplete.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountRegisterComplete::class;
$config->requireAuth = false;
