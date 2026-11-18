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
        $query->stmt->alias("(SELECT concat(sum(oi.qty),' бр.') FROM order_items oi WHERE oi.orderID = orders.orderID)", "item_count");
        $query->stmt->where()->match("userID", Module::Active()->getUserID());

        $view = new TableView($query);
        $view->setDefaultOrder(new OrderColumn("order_date", OrderDirection::DESC));

        $view->addColumn(new TableColumn("orderID", tr("Номер")));
        $view->addColumn(new TableColumn("order_date", tr("Дата")));
        $view->addColumn(new TableColumn("item_count", tr("Продукти")));
        $view->addColumn(new TableColumn("total", tr("Сума")));
        $view->addColumn(new TableColumn("status", tr("Status")));
        $view->addColumn(new TableColumn("actions", tr("Actions")));

        $view->getColumn("order_date")->setCellRenderer(new DateCell());
        $view->getColumn("status")->getCellRenderer()->translation_enabled = true;

        $act = new ActionsCell();
        $act->getActions()->append(new Action(tr("Покажи детайли"), Module::PathURL("orders/details")->toString(), array(new DataParameter("orderID"))));
        $view->getColumn("actions")->setCellRenderer($act);

        $this->addColumn(AccountContent::Column(tr("История на поръчките"), $view));
    }

    protected function getContentTitle() : string
    {
        return tr("История на поръчките");
    }
}
