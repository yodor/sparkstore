<?php
include_once("store/beans/InstockSubscribersBean.php");
include_once("store/mailers/InstockProductMailer.php");
include_once("store/beans/BackInstockProductsBean.php");

class CheckStockState
{
    protected int $prodID = -1;
    protected string $product_name = "";
    protected string $product_link = "";

    public function __construct(int $prodID, string $product_name)
    {
        $this->prodID = $prodID;
        $this->product_name = $product_name;
        $this->product_link = fullURL(LOCAL."/products/details.php?prodID={$this->prodID}");
    }

    public function notify()
    {
        debug("Notify starting ...");

        $bean = new InstockSubscribersBean();
        $query = $bean->query($bean->key(), "email");
        $query->select->where()->add("prodID", $this->prodID);
        $num = $query->exec();

        debug("Going to notify ".$num." subscribers ...");
        $mailer = new InstockProductMailer();
        $mailer->setProduct($this->product_name, $this->product_link);

        while ($result = $query->nextResult()) {

            $client_email = $result->get("email");

            try {
                $mailer->setRecipient($client_email);
                $mailer->prepareMessage();
                $mailer->send();
            }
            catch (Exception $e) {
                debug("Unable to send notification email to subscriber: ".$client_email." | Error: ".$e->getMessage());
            }

            try {
                $bean->delete($result->get($bean->key()));
            }
            catch (Exception $e) {
                debug("Unable to delete subscriber: ".$client_email);
            }
        }

        debug("Notify finished ...");

    }
    public function process(int $stock_amount, int $old_stock_amount)
    {
        debug("Transacted stock_amount: $stock_amount | Loaded stock_amount: $old_stock_amount");

        $bispb = new BackInstockProductsBean();

        if ($old_stock_amount == 0 && $stock_amount > 0) {
            debug("Product is back in stock ...");

            try {
                $this->notify();
            }
            catch (Exception $e) {
                debug("Error notifiying subscribers: ".$e->getMessage());
            }

            try {
                $bispb->backinstock($this->prodID);
            }
            catch (Exception $e) {
                debug("Error updating backinstock list: ".$e->getMessage());
            }

        }
        else if ($old_stock_amount > 0 && $stock_amount == 0) {
            debug("Product is out of stock ...");
            try {
                $bispb->outofstock($this->prodID);
            }
            catch (Exception $e) {
                debug("Error deleting from backinstock list: ".$e->getMessage());
            }


        }
        else {
            debug("No need to call InstockSubscribers mailer");
        }
    }
}
?>