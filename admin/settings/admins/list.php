<?php
include_once("session.php");
include_once("templates/admin/AdminUsersListPage.php");
$cmp = new AdminUsersListPage();
$cmp->getPage()->navigation()->clear();
$cmp->render();
?>
