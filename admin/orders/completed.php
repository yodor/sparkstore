<?php
include_once("store/components/OrdersListPage.php");


$page = new OrdersListPage();

$page->getOrderListSQL()->where()->match("status", OrdersBean::STATUS_COMPLETED);

$page->render();