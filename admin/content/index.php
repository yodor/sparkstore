<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");

$page = new AdminPage();

$menu = array(new MenuItem("Menu Items", "menus/index.php", "menu"),
              new MenuItem("Gallery Photos", "photo_gallery/list.php", "gallery"),

              new MenuItem("Dynamic Pages", "pages/list.php", "list"),

              new MenuItem("FAQ", "faq/list.php", "list"),

              new MenuItem("News", "news/list.php", "list"),

              new MenuItem("Config", "config/index.php", "list"),

              new MenuItem("Logo", "logo/list.php", "list"),

);

$page->setPageMenu($menu);

$page->navigation()->clear();

$page->startRender();
echo "Content Management";
$page->finishRender();