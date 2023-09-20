<?php
include_once("session.php");
include_once("templates/admin/DynamicPageList.php");

$cmp = new DynamicPageList();

$cmp->getPage()->navigation()->clear();

$cmp->render();

?>
