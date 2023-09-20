<?php
include_once("session.php");
include_once("auth/UserAuthenticator.php");

$auth = new UserAuthenticator();
$auth->logout();

header("Location: " . LOCAL."/");
exit;

?>
