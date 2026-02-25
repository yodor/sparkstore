<?php
include_once("pages/AdminLoginForgotPassword.php");

$page = new AdminLoginForgotPassword();
$page->initialize();
$page->render();