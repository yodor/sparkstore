<?php
include_once("store/templates/modules/account/content/AccountRegister.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountRegister::class;
$config->requireAuth = false;
