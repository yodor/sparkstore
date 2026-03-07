<?php
include_once("templates/Template.php");

//define admin module
include_once("templates/Module.php");
$adminModule = Module::Factory("admin", Spark::PathParts(Spark::Get(Config::LOCAL), "admin2"));

$adminModule->pageClass = SparkTemplateAdminPage::class;
$adminModule->authClass = AdminAuthenticator::class;

//include init.php from module prefix template/modules
Module::Initialize();
//authorize
Module::Authorize();
//reponse
Module::Response();