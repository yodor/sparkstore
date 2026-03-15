<?php
include_once("store/components/OrdersListPage.php");
$page = new OrdersListPage();

$view = $page->initView();

$actions = $page->viewItemActions();

$page->render();