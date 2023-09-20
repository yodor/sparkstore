<?php
if (!isset($info)) {
    $info = pathinfo($_SERVER['SCRIPT_FILENAME']);
}
$info = pathinfo($info["dirname"]);
$parent = $info["dirname"];

include_once($parent."/session.php");

include_once("store/templates/admin/TemplateFactory.php");
?>