<?php
//
// admin module - store part, included after the core init.php: the store admin look
//
$module = Module::Active();
$module->addPageCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/AdminTemplatePageBase.css");
//no zoom on phones
$module->addPageMeta("viewport", "width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0");
