<?php
include_once("responders/RequestResponder.php");

include_once("store/mailers/OrderStatusMailer.php");

class OrderStatusRequestResponder extends RequestResponder
{

    protected int $orderID = -1;
    protected string $status = "";

    public function __construct()
    {
        parent::__construct("order_status");
    }

    public function getParameterNames(): array
    {
        return parent::getParameterNames() + array("orderID", "status");
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
        $this->status = $this->url->get("status")->value();
    }

    protected function processImpl() : void
    {

        $db = DBConnections::Open();

        try {

            $db->transaction();

            $update_row = array();

            $bean = new OrdersBean();

            $update_row["status"] = $db->escape($this->status);
            $update_row["completion_date"] = $db->dateTime();

            if (!$bean->update($this->orderID, $update_row, $db)) throw new Exception("Unable to update this order: " . $db->getError());

            $m = new OrderStatusMailer($this->orderID, $this->status);
            $m->send();

            $db->commit();

            Session::set("alert", tr("Статусът на поръчката беше обновен") . "<BR>" . tr("Потвърждаващ e-mail беше изпратен на клиента"));

        }
        catch (Exception $e) {

            $db->rollback();
            throw $e;
        }

    }

}

?>
