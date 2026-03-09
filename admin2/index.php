<?php
include_once("templates/Template.php");

//define admin module
include_once("templates/Module.php");
$adminModule = Module::Factory("admin", "admin2/");

$adminModule->pageClass = SparkTemplateAdminPage::class;
$adminModule->authClass = AdminAuthenticator::class;

$adminModule->initialize();

//response
Module::Response();