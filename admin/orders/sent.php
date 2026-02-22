<?php
include_once("store/components/OrdersListPage.php");


$page = new OrdersListPage();


$page->getOrderListSQL()->where()->add("status", "'" . OrdersBean::STATUS_SENT . "'");

$view = $page->initView();
$actions = $page->viewItemActions();


$actions->append(new Action("Потвърди завършване", "?cmd=order_status", array(new DataParameter("orderID"),
                                                                                        new URLParameter("status", OrdersBean::STATUS_COMPLETED),))

);


$page->render();