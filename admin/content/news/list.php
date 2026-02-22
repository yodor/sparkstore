<?php
include_once("components/templates/admin/NewsItemsListPage.php");
include_once("beans/NewsItemsBean.php");

$cmp = new NewsItemsListPage();
$cmp->getPage()->navigation()->clear();
$cmp->render();