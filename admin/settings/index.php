<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");

$page = new AdminPage("Settings");

$menu = array(

    new MenuItem("Administrators", "admins/list.php", "admin_users"),
    new MenuItem("Languages", "languages/list.php", "language"),
    new MenuItem("SEO", "seo.php", "seo"),

);

$page->setPageMenu($menu);

$page->navigation()->clear();

$page->startRender();

$page->finishRender();

?>
