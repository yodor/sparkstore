<?php
include_once("session.php");
include_once("templates/admin/NewsItemsListPage.php");
include_once("beans/NewsItemsBean.php");

$cmp = new NewsItemsListPage();
$cmp->getPage()->navigation()->clear();
$cmp->render();

?>
