<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");

$menu = array(new MenuItem("Main Menu", "main/list.php", "menu"),);

$page = new AdminPage();

$page->setPageMenu($menu);

$page->navigation()->clear();

$page->startRender();

$page->finishRender();