<?php
include_once("responders/RequestResponder.php");

include_once("store/mailers/OrderStatusMailer.php");

class OrderStatusRequestResponder extends RequestResponder
{

    protected $orderID = -1;
    protected $status = NULL;

    public function __construct()
    {
        parent::__construct("order_status");
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function parseParams() : void
    {
        if (!isset($_GET["orderID"])) throw new Exception("Order ID not passed");
        $this->orderID = (int)$_GET["orderID"];

        if (!isset($_GET["status"])) throw new Exception("Order status not passed");
        $this->status = $_GET["status"];

        $arr = $_GET;
        unset($arr["cmd"]);
        unset($arr["orderID"]);
        unset($arr["status"]);
        $this->cancel_url = queryString($arr);
        $this->cancel_url = $_SERVER['PHP_SELF'] . $this->cancel_url;

    }

    protected function processImpl()
    {

        $db = DBConnections::factory();

        try {

            $db->transaction();

            $update_row = array();

            $bean = new OrdersBean();

            $update_row["status"] = $this->status;
            $update_row["completion_date"] = $db->dateTime();

            if (!$bean->updateRecord($this->orderID, $update_row, $db)) throw new Exception("Unable to update this order: " . $db->getError());

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
