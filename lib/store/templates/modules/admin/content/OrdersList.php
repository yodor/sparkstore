<?php
include_once("templates/content/BeanList.php");
include_once("store/beans/OrdersBean.php");
include_once("store/utils/OrderListSQL.php");
include_once("store/responders/OrderStatusRequestResponder.php");
include_once("store/components/renderers/cells/OrderClientCell.php");
include_once("components/renderers/cells/DateCell.php");

/**
 * Orders, optionally of one status (setStatus()) - with the status changes allowed from it
 */
class OrdersList extends BeanList
{
    /**
     * Status changes offered for the orders of a status: status => array(new status => action title)
     */
    const array TRANSITIONS = array(
        OrdersBean::STATUS_PROCESSING => array(OrdersBean::STATUS_SENT => "Confirm Sending", OrdersBean::STATUS_CANCELED => "Cancel Order"),
        OrdersBean::STATUS_SENT => array(OrdersBean::STATUS_COMPLETED => "Потвърди завършване"),
    );

    protected string $status = "";

    protected OrderListSQL $orderList;

    protected OrderStatusRequestResponder $statusResponder;

    public function __construct()
    {
        parent::__construct();
        $this->orderList = new OrderListSQL();
        $this->statusResponder = new OrderStatusRequestResponder();
        $this->addEnabled = false;
        $this->editEnabled = false;
    }

    /**
     * Show only the orders with status '$status'
     * @param string $status
     * @return void
     */
    public function setStatus(string $status) : void
    {
        $this->status = $status;
        $this->orderList->where()->match("status", $status);
    }

    public function setup(TemplateConfig $config): void
    {
        $config->beanClass = OrdersBean::class;
        $config->iterator = new SelectQuery($this->orderList, "orderID", "orders");
        $config->listFields = array("order_date" => "Order Date", "userID" => "Client", "total" => "Total", "status" => "Status");
        $config->searchField = array("orderID", "items", "client", "delivery_address");
        parent::setup($config);
    }

    protected function initItemActions(ActionCollection $act): void
    {
        $details = TemplateContent::CreateAction("details", tr("Details"), "/orders/details");
        $details->getURL()->add(new DataParameter("orderID"));
        $act->append($details);

        foreach (OrdersList::TRANSITIONS[$this->status] ?? array() as $status => $title) {
            $act->append(Action::RowSeparator());
            $action = $this->statusResponder->createStatusAction(tr($title), $status);
            $action->setURL(Module::PathURL("", $action->getURL()));
            $act->append($action);
        }
    }

    public function initialize(): void
    {
        parent::initialize();
        $view = $this->tableView();
        $view->getColumn("order_date")->setCellRenderer(new DateCell());
        $view->getColumn("userID")->setCellRenderer(new OrderClientCell());
        $view->setDefaultOrder(new OrderColumn("order_date", OrderDirection::DESC));
    }

    protected function getContentTitle(): string
    {
        return tr("Orders");
    }
}
