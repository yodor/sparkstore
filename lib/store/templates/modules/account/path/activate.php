<?php
include_once("store/templates/modules/account/content/AccountActivate.php");
$config = TemplateConfig::Factory();
$config->contentClass = AccountActivate::class;
$config->requireAuth = false;
