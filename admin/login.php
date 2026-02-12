<?php
include_once("session.php");
include_once("pages/AdminLoginPage.php");

$page = new AdminLoginPage();
$page->initialize();
$page->render();