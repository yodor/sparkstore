<?php
//
// admin module - the shop menu, included after the store menu.php: Orders (with the delivery settings and the courier
// offices) and Clients after Store
//
include_once("utils/menu/MenuItem.php");
include_once("utils/menu/MenuItemList.php");
include_once("pages/SparkAdminPage.php");

$menu = SparkAdminPage::Menu() ?? new MenuItemList();

$orders = new MenuItem("Orders", "orders");
$orders->append(new MenuItem("Active Orders", "active"));
$orders->append(new MenuItem("Sent Orders", "sent"));
$orders->append(new MenuItem("Completed Orders", "completed"));
$orders->append(new MenuItem("Canceled Orders", "canceled"));
$orders->append(new MenuItem("All Orders", "all"));
$delivery = new MenuItem("Delivery", "delivery");
//the office lists of the couriers (the checkout office step)
$delivery->append(new MenuItem("Courier Offices", "offices"));
$orders->append($delivery);

$menu->insertAfter("store", $orders);
$menu->insertAfter("orders", new MenuItem("Clients", "clients"));

SparkAdminPage::SetMenu($menu);
