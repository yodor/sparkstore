<?php
include_once("store/beans/InstockSubscribersBean.php");
include_once("store/mailers/InstockProductMailer.php");
include_once("store/beans/BackInstockProductsBean.php");
include_once("store/utils/url/ProductURL.php");
include_once("components/BeanFormEditor.php");
include_once("objects/events/BeanFormEditorEvent.php");

/**
 * Stock amount change of a product, made in the admin product editor:
 *  0 -> above 0: mails the in-stock subscribers (InstockProductMailer), removes them and adds the product to
 *               the back in stock list (backinstock_products)
 *  above 0 -> 0: removes the product from the back in stock list
 * Observe() attaches it to a product editor.
 */
class CheckStockState
{
    protected int $prodID = -1;
    protected string $product_name = "";
    protected ?ProductURL $product_link = null;

    public function __construct(int $prodID, string $product_name)
    {
        $this->prodID = $prodID;

        $this->product_name = $product_name;

        $this->product_link = new ProductURL();
        $this->product_link->setProductID($this->prodID);
        if ($product_name) {
            $this->product_link->setProductName($product_name);
        }
    }

    /**
     * Watch a product editor: remember the stock amount loaded with the product and process the change after
     * the product is saved. Nothing to do for a new product or a form without a 'stock_amount' input.
     * An error here is logged - the product is already saved.
     *
     * @param BeanFormEditor $editor
     * @return void
     */
    public static function Observe(BeanFormEditor $editor) : void
    {
        if (!$editor->getForm()->haveInput("stock_amount")) return;

        //stock amount before the edit, -1 = not loaded (new product)
        $old_stock_amount = -1;

        SparkEventManager::register(BeanFormEditorEvent::class, new SparkObserver(function (BeanFormEditorEvent $event) use ($editor, &$old_stock_amount) {
            if ($event->getSource() !== $editor) return;
            $form = $editor->getForm();

            if ($event->isEvent(BeanFormEditorEvent::FORM_BEAN_LOADED)) {
                $old_stock_amount = (int)$form->getInput("stock_amount")->getValue();
            }
            else if ($event->isEvent(BeanFormEditorEvent::FORM_BEAN_TRANSACED)) {
                $prodID = $editor->getEditID();
                if ($prodID < 1 || $old_stock_amount < 0) return;
                try {
                    $product_name = $form->haveInput("product_name") ? (string)$form->getInput("product_name")->getValue() : "";
                    $check = new CheckStockState($prodID, $product_name);
                    $check->process((int)$form->getInput("stock_amount")->getValue(), $old_stock_amount);
                }
                catch (Exception $e) {
                    Debug::ErrorLog("Stock state check failed for prodID: $prodID | " . $e->getMessage());
                }
            }
        }));
    }

    public function notify() : void
    {
        Debug::ErrorLog("Notify starting ...");

        $bean = new InstockSubscribersBean();
        $query = $bean->query($bean->key(), "email");
        $query->stmt->where()->match("prodID", $this->prodID);
        $query->exec();

        Debug::ErrorLog("Going to notify subscribers ...");
        $mailer = new InstockProductMailer();
        $mailer->setProduct($this->product_name, $this->product_link);

        $notified = 0;
        while ($result = $query->nextResult()) {

            $client_email = $result->get("email");

            try {
                $mailer->setRecipient($client_email);
                $mailer->prepareMessage();
                $mailer->send();
                $notified++;
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

        Debug::ErrorLog("Notified [$notified] subscribers ...");

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