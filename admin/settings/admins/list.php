<?php
include_once("components/templates/admin/AdminUsersListPage.php");
$cmp = new AdminUsersListPage();
$cmp->getPage()->navigation()->clear();
$cmp->render();