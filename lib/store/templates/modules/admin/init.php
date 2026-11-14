<?php
//
// admin module - store part, included after the core init.php
//
include_once("store/pages/AdminTemplatePageBase.php");

$module = Module::Active();
$module->pageClass = AdminTemplatePageBase::class;
