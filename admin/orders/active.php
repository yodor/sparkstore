<?php
include_once("session.php");
include_once("store/components/OrdersListPage.php");


$page = new OrdersListPage();


$page->getOrderListSQL()->where()->add("status", "'" . OrdersBean::STATUS_PROCESSING . "'");


$view = $page->initView();

$actions = $page->viewItemActions();

$actions->append(
    new Action(tr("Confirm Sending"), "?cmd=order_status",
               array(new DataParameter("orderID"), new URLParameter("status", OrdersBean::STATUS_SENT)))
);

$actions->append(new RowSeparator());

$actions->append(
    new Action(tr("Cancel Order"), "?cmd=order_status",
               array(new DataParameter("orderID"), new URLParameter("status", OrdersBean::STATUS_CANCELED)))
);


$page->render();
?>
