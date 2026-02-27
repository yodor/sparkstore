<?php
include_once("class/pages/AdminPage.php");

$page = new AdminPage();

$menu = array(new MenuItem("Базови", "base.php", "list"),
              new MenuItem("Маркетинг", "marketing.php", "list"),
);

$page->setPageMenu($menu);

$page->navigation()->clear();

$page->startRender();
echo tr("Configuration");
$page->finishRender();