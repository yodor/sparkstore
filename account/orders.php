<?php
include_once("session.php");
include_once("class/pages/AccountPage.php");

include_once("components/TableView.php");
include_once("components/renderers/cells/DateCell.php");

include_once("store/beans/ClientAddressesBean.php");
include_once("store/beans/CourierAddressesBean.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("store/beans/OrdersBean.php");


$page = new AccountPage();
$page->setTitle(tr("История на поръчките"));

//invoke OrdersBean before OrderItemsBean to allow auto creation of the table structure
$orders = new OrdersBean();
$order_items = new OrderItemsBean();

$courier_addresses = new CourierAddressesBean();
$client_addresses = new ClientAddressesBean();
$invoices = new InvoiceDetailsBean();


$clients = new UsersBean();

$sel = new SQLSelect();

$sel->fields()->set("*");
$sel->fields()->setExpression(" (SELECT concat(sum(oi.qty),' бр.') FROM order_items oi WHERE oi.orderID = o.orderID) ", "item_count");
$sel->from = " orders o ";
$sel->where()->add("o.userID", $page->getUserID());
$sel->order_by = " 'Processing', 'Sent', 'Completed' ";

$view = new TableView(new SQLQuery($sel, "orderID"));

// $view->setCaption($caption);

$view->setDefaultOrder(" order_date DESC ");

$view->addColumn(new TableColumn("orderID", "Номер"));

$view->addColumn(new TableColumn("order_date", "Дата"));

$view->addColumn(new TableColumn("item_count", "Продукти"));

$view->addColumn(new TableColumn("total", "Сума"));

$view->addColumn(new TableColumn("status", "Status"));

$view->addColumn(new TableColumn("actions", "Actions"));

// $view->getColumn("is_confirmed")->setCellRenderer(new BooleanCell("Yes", "No"));
// $view->getColumn("require_invoice")->setCellRenderer(new BooleanCell("Yes", "No"));
$view->getColumn("order_date")->setCellRenderer(new DateCell());


$act = new ActionsCell();

$act->getActions()->append(new Action("Покажи детайли", "order_details.php", array(new DataParameter("orderID"))));

$view->getColumn("actions")->setCellRenderer($act);

$view->getColumn("status")->getCellRenderer()->translation_enabled = true;

$page->startRender();

echo "<div class='column'>";
echo "<h1 class='Caption'>" . $page->getTitle() . "</h1>";
echo "<div class='panel'>";
$view->render();
echo "</div>";
echo "</div>";

$page->finishRender();