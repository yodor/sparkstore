<?php
include_once("pages/SparkTemplateAdminPage.php");
include_once("templates/TemplateEvent.php");

$page = new SparkTemplateAdminPage();

//fire TemplateEvent::MENU_CREATED
include_once("menu_items.php");

include_once("path_templates.php");

$path = "";
if (isset($_GET["path"])) $path = $_GET["path"];
$editID = null;
if (isset($_GET["editID"])) $editID = (int)$_GET["editID"];


$config = PathHandler::Config($path, $editID);
if ($config instanceof TemplateConfig) {
    $config->path = $path;
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONFIG_CREATED, $config));
}

$content = Template::Content($path, $config);
if ($content instanceof TemplateContent) {
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONTENT_CREATED, $content));

    $content->initPageActions($page->getActions());
    $content->initPageFilters($page->getPageFilters());

    $content->initialize();
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONTENT_INITIALIZED, $content));

    $page->items()->append($content->component());
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONTENT_INSERTED, $content));

    $page->setName($content->getConfig()->title);

    $content->processInput();
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONTENT_INPUT_PROCESSED, $content));
}

$page->render();