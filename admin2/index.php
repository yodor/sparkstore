<?php
include_once("pages/SparkTemplateAdminPage.php");
include_once("templates/Template.php");


$page = new SparkTemplateAdminPage();

//fire TemplateEvent::MENU_CREATED
include_once("menu_items.php");

$path = "";
if (isset($_GET["path"])) $path = $_GET["path"];
$editID = null;
if (isset($_GET["editID"])) $editID = (int)$_GET["editID"];

Spark::EnableBeanLocation("class/forms/");
Spark::EnableBeanLocation("store/forms/");
Spark::EnableBeanLocation("forms/");

include_once("store/responders/json/AdminHelpResponder.php");
$responder = new AdminHelpResponder();

if (!$path) {
    $path = "home";
}

$content = null;

try {
    $config = Template::Config($path, $editID);
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONFIG_CREATED, $config));

    $content = Template::Content($path, $config);
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONTENT_CREATED, $content));

    $content->initialize();
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONTENT_INITIALIZED, $content));

    $content->processInput();
    SparkEventManager::emit(new TemplateEvent(TemplateEvent::CONTENT_INPUT_PROCESSED, $content));
}
catch (Exception $e) {
    $content = Template::Content($path, Template::Plain("Error:$path", $e->getMessage()));
}

if (is_null($content)) {
    $content = Template::Content($path, Template::Plain("Error:$path", "No content initialized"));
}

$page->update($content);

$page->render();