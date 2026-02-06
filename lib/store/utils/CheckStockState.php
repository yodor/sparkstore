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
        $this->product_link = new ProductURL();
        $this->product_link->setProductID($this->prodID);
    }

    public function notify() : void
    {
        Debug::ErrorLog("Notify starting ...");

        $bean = new InstockSubscribersBean();
        $query = $bean->query($bean->key(), "email");
        $query->select->where()->add("prodID", $this->prodID);
        $num = $query->exec();

        Debug::ErrorLog("Going to notify ".$num." subscribers ...");
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
                Debug::ErrorLog("Unable to send notification email to subscriber: ".$client_email." | Error: ".$e->getMessage());
            }

            try {
                $bean->delete($result->get($bean->key()));
            }
            catch (Exception $e) {
                Debug::ErrorLog("Unable to delete subscriber: ".$client_email);
            }
        }

        Debug::ErrorLog("Notify finished ...");

    }
    public function process(int $stock_amount, int $old_stock_amount) : void
    {
        Debug::ErrorLog("Transacted stock_amount: $stock_amount | Loaded stock_amount: $old_stock_amount");

        $bispb = new BackInstockProductsBean();

        if ($old_stock_amount == 0 && $stock_amount > 0) {
            Debug::ErrorLog("Product is back in stock ...");

            try {
                $this->notify();
            }
            catch (Exception $e) {
                Debug::ErrorLog("Error notifying subscribers: ".$e->getMessage());
            }

            try {
                $bispb->backinstock($this->prodID);
            }
            catch (Exception $e) {
                Debug::ErrorLog("Error updating backinstock list: ".$e->getMessage());
            }

        }
        else if ($old_stock_amount > 0 && $stock_amount == 0) {
            Debug::ErrorLog("Product is out of stock ...");
            try {
                $bispb->outofstock($this->prodID);
            }
            catch (Exception $e) {
                Debug::ErrorLog("Error deleting from backinstock list: ".$e->getMessage());
            }


        }
        else {
            Debug::ErrorLog("No need to call InstockSubscribers mailer");
        }
    }
}
?>