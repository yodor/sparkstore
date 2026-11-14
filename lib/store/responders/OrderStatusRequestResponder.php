<?php
include_once("responders/RequestResponder.php");

include_once("store/mailers/OrderStatusMailer.php");
include_once("store/beans/OrdersBean.php");

class OrderStatusRequestResponder extends RequestResponder
{

    /**
     * Statuses an order can be set to
     */
    const array STATUSES = array(OrdersBean::STATUS_PROCESSING, OrdersBean::STATUS_SENT, OrdersBean::STATUS_COMPLETED, OrdersBean::STATUS_CANCELED);

    protected int $orderID = -1;
    protected string $status = "";

    public function __construct()
    {
        parent::__construct();
    }

    public function getParameterNames(): array
    {
        $result = parent::getParameterNames();
        $result[] = "orderID";
        $result[] = "status";
        return $result;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function parseParams() : void
    {
        if (!$this->url->contains("orderID")) throw new Exception("Order ID not passed");
        $this->orderID = (int)$this->url->get("orderID")->value();

        if (!$this->url->contains("status")) throw new Exception("Order status not passed");
        $this->status = (string)$this->url->get("status")->value();
        if (!in_array($this->status, OrderStatusRequestResponder::STATUSES, true)) throw new Exception("Incorrect order status");
    }

    protected function processImpl() : void
    {

        $db = DBManager::Driver();

        try {

            $db->transaction();

            $update_row = array();

            $bean = new OrdersBean();

            $update_row["status"] = $this->status;
            $update_row["completion_date"] = new DBExpression("now()");

            if (!$bean->update($this->orderID, $update_row, $db)) throw new Exception("Unable to update this order: " . $db->getError());

            $m = new OrderStatusMailer($this->orderID, $this->status);
            $m->send();

            $db->commit();

            Session::SetAlert(tr("Статусът на поръчката беше обновен") . "<BR>" . tr("Потвърждаващ e-mail беше изпратен на клиента"));

        }
        catch (Exception $e) {

            $db->rollback();
            throw $e;
        }

    }

    /**
     * Item action setting the order of the row to '$status'
     * @param string $title
     * @param string $status
     * @return Action
     */
    public function createStatusAction(string $title, string $status) : Action
    {
        $action = parent::createAction($title);
        $action->getURL()->add(new DataParameter("orderID"));
        $action->getURL()->add(new URLParameter("status", $status));
        return $action;
    }
}
