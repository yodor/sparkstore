<?php
include_once("responders/RequestResponder.php");

include_once("store/mailers/OrderCompletionMailer.php");

class ConfirmSendRequestResponder extends RequestResponder
{

    protected int $orderID = -1;

    public function __construct()
    {
        parent::__construct("confirm_send");
    }

    public function getParameterNames(): array
    {
        return parent::getParameterNames() + array("orderID");
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function parseParams() : void
    {
        if (!$this->url->contains("orderID")) throw new Exception("Order ID not passed");
        $this->orderID = (int)$this->url->get("orderID")->value();
    }

    protected function processImpl() : void
    {

        //$db = DBConnections::Get();

        try {
            // 	  $field_name = $db->escapeString($this->field_name);
            //
            // 	  $update_row = array();
            //
            // 	  $update_row[$field_name]=$this->status;
            // 	  $update_row["completion_date"]=$db->dateTime();
            //
            // 	  if (!$this->bean->updateRecord($this->item_id, $update_row, $db))throw new Exception("Unable to update this order: ".$db->getError());
            //
            //
            // 	  $m = new OrderCompletionMailer($this->item_id);
            // 	  $m->send();
            //
            //
            // 	  $db->commit();

            Session::SetAlert(tr("Поръчката беше маркирана като изпратена") . "<BR>" . tr("Потвърждаващ e-mail беше изпратен на клиента"));

        }
        catch (Exception $e) {

            //$db->rollback();
            throw $e;
        }

    }

}

?>
