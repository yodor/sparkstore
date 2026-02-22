<?php
include_once("auth/UserAuthenticator.php");

$auth = new UserAuthenticator();
$auth->logout();
Session::Destroy();

header("Location: " . Spark::Get(Config::LOCAL)."/");
exit;