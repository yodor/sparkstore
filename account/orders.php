<?php
include_once("class/pages/AccountPage.php");

include_once("components/TableView.php");
include_once("components/renderers/cells/DateCell.php");

include_once("store/beans/OrdersBean.php");


$page = new AccountPage();
$page->setTitle(tr("История на поръчките"));

$orders = new OrdersBean();

$query = $orders->queryFull();

//pieces of each order (the outer column needs its table name - inside the subquery 'orderID' is oi.orderID)
$query->stmt->alias("(SELECT concat(sum(oi.qty),' бр.') FROM order_items oi WHERE oi.orderID = orders.orderID)", "item_count");

$query->stmt->where()->match("userID", $page->getUserID());
//$query->stmt->orderColumn(new OrderField("status", "Processing", "Sent", "Completed"));

$view = new TableView($query);

// $view->setCaption($caption);

$view->setDefaultOrder(new OrderColumn("order_date", OrderDirection::DESC));

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