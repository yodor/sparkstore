<?php
include_once("auth/AdminAuthenticator.php");

$auth = Module::Active()->getAuthenticator();
$auth->logout();

header("Location: " . Module::PathURL("login"));
exit;