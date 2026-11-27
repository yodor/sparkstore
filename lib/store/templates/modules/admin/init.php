<?php
//
// admin module - store part, included after the core init.php: the store admin look
//
include_once("templates/Template.php");
include_once("store/utils/CatalogParts.php");

$module = Module::Active();

//the catalog parts the site does not use (CatalogParts::Disable in its site init): their admin pages answer 404
//and leave the menu
foreach (CatalogParts::DisabledPaths() as $path) {
    Template::DisablePath($path);
}
$module->addPageCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/AdminTemplatePageBase.css");
//no zoom on phones
$module->addPageMeta("viewport", "width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0");
//the marketing settings moved to Settings > Marketing (core)
$module->addRedirect("content/config/marketing", "settings/marketing");
