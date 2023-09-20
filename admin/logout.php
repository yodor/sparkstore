<?php
include_once("session.php");
include_once("auth/AdminAuthenticator.php");

$auth = new AdminAuthenticator();
$auth->logout();

if (isset($_SESSION["upload_control"])) {
    unset($_SESSION["upload_control"]);
}
if (isset($_SESSION["upload_control_removed"])) {
    unset($_SESSION["upload_control_removed"]);
}

header("Location: " . LOCAL . "/admin/");
exit;

?>
