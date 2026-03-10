<?php
include_once("auth/AdminAuthenticator.php");

$auth = new AdminAuthenticator();
$auth->logout();

header("Location: " . Spark::Get(Config::ADMIN_LOCAL));
exit;