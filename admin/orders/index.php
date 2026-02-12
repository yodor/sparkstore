<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");

$page = new AdminPage();

$menu = array(new MenuItem("Активни", "active.php", "class:orders icon_confirmed"),
              new MenuItem("Изпратени", "sent.php", "class:orders icon_confirmed"),
              new MenuItem("Завършени", "completed.php", "class:orders icon_completed"),
              new MenuItem("Отказани", "canceled.php", "class:orders icon_completed"),
              new MenuItem("Всички", "all.php", "class:orders icon_all"),
              new MenuItem("Доставки", "delivery.php", "class:orders icon_delivery"),

);

$page->setPageMenu($menu);

$page->navigation()->clear();

$page->startRender();

echo tr("Управление на поръчки");

$page->finishRender();