<?php
include_once("templates/Template.php");

Template::ModuleInit(Spark::GetObject(Config::MODULE_ADMIN));
Template::ModuleResponse();