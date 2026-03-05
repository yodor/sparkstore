<?php
include_once("templates/Template.php");

Template::ModuleInit(Spark::GetObject(Config::MODULE_ADMIN));

Template::ModuleResponse();

//include_once("store/pages/AdminTemplatePageBase.php");
//
//$page = new AdminTemplatePageBase();
//
//$page->initialize();
//
//$page->render();