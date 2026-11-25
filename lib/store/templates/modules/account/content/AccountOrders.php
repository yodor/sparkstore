<?php
include_once("store/templates/modules/account/content/AccountContent.php");
include_once("components/TableView.php");
include_once("components/renderers/cells/DateCell.php");
include_once("components/renderers/cells/ActionsCell.php");
include_once("store/beans/OrdersBean.php");

/**
 * Order history of the client
 */
class AccountOrders extends AccountContent
{
    public function initialize() : void
    {
        $orders = new OrdersBean();
        $query = $orders->queryFull();

        //pieces of each order (the outer column needs its table name - inside the subquery 'orderID' is oi.orderID)
        $pieces = str_replace(array("\\", "'"), array("\\\\", "''"), tr("pcs."));
        $query->stmt->alias("(SELECT concat(sum(oi.qty),' " . $pieces . "') FROM order_items oi WHERE oi.orderID = orders.orderID)", "item_count");
        $query->stmt->where()->match("userID", Module::Active()->getUserID());

        $view = new TableView($query);
        $view->setDefaultOrder(new OrderColumn("order_date", OrderDirection::DESC));

        $view->addColumn(new TableColumn("orderID", tr("Number")));
        $view->addColumn(new TableColumn("order_date", tr("Date")));
        $view->addColumn(new TableColumn("item_count", tr("Products")));
        $view->addColumn(new TableColumn("total", tr("Amount")));
        $view->addColumn(new TableColumn("status", tr("Status")));
        $view->addColumn(new TableColumn("actions", tr("Actions")));

        $view->getColumn("order_date")->setCellRenderer(new DateCell());
        $view->getColumn("status")->getCellRenderer()->translation_enabled = true;

        $act = new ActionsCell();
        $act->getActions()->append(new Action(tr("Show details"), Module::PathURL("orders/details")->toString(), array(new DataParameter("orderID"))));
        $view->getColumn("actions")->setCellRenderer($act);

        $this->addColumn(AccountContent::Column(tr("Order history"), $view));
    }

    protected function getContentTitle() : string
    {
        return tr("Order history");
    }
}
