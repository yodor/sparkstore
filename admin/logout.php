<?php
include_once("session.php");
include_once("auth/AdminAuthenticator.php");

$auth = new AdminAuthenticator();
$auth->logout();
Session::Destroy();

header("Location: " . LOCAL . "/admin/");
exit;

?>
