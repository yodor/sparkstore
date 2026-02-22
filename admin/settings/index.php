<?php
include_once("class/pages/AdminPage.php");

$page = new AdminPage();

$menu = array(

    new MenuItem("Administrators", "admins/list.php", "admin_users"),
    new MenuItem("Languages", "languages/list.php", "language"),

);

$page->setPageMenu($menu);

$page->navigation()->clear();

$page->startRender();

$page->finishRender();