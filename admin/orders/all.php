<?php
include_once("session.php");
include_once("store/components/OrdersListPage.php");


$page = new OrdersListPage();

//$page->getOrderListSQL()->where()->add("status", "'" . OrdersBean::STATUS_PROCESSING . "'");

$view = $page->initView();

$actions = $page->viewItemActions();

$page->render();