<?php
include_once("session.php");
include_once("auth/AdminAuthenticator.php");

$auth = new AdminAuthenticator();
$auth->logout();

if (isset($_SESSION["upload_control"])) {
    unset($_SESSION["upload_control"]);
}

header("Location: " . LOCAL . "/admin/");
exit;

?>
