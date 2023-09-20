<?php
include_once("session.php");
include_once("templates/admin/FAQItemsListPage.php");
include_once("beans/FAQItemsBean.php");

$cmp = new FAQItemsListPage();

$cmp->getPage()->navigation()->clear();

$cmp->render();
?>