<?php
include_once("session.php");
include_once("components/templates/admin/AdminUserEditorPage.php");

$cmp = new AdminUserEditorPage();
$cmp->render();