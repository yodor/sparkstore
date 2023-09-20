<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");

$page = new AdminPage();

$page->setPageMenu(TemplateFactory::MenuForPage("Store"));

$page->navigation()->clear();

$page->startRender();

echo "Управление на магазина";

$page->finishRender();
?>