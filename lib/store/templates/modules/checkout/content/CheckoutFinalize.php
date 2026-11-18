<?php
include_once("store/templates/modules/checkout/content/CheckoutContent.php");
include_once("store/templates/modules/checkout/content/CheckoutConfirm.php");
include_once("store/utils/OrderProcessor.php");
include_once("store/mailers/OrderConfirmationMailer.php");
include_once("store/mailers/OrderConfirmationAdminMailer.php");
include_once("store/mailers/OrderErrorAdminMailer.php");

/**
 * The order - only from the order button of confirm (POST with the note). Every step must be filled. The
 * order number is kept in the session for complete. A failure: a short message to the client, the details
 * in the log and the order error mail.
 */
class CheckoutFinalize extends CheckoutContent
{
    public function initialize() : void
    {
        if (strcmp((string)($_SERVER["REQUEST_METHOD"] ?? "GET"), "POST") !== 0) $this->redirect("confirm");

        $this->ensureCartItems();
        $missing = CheckoutContent::MissingStep(Module::Active()->getUserID());
        if ($missing !== "") $this->redirect($missing);
    }

    public function processInput() : void
    {
        parent::processInput();

        $cart = Cart::Instance();

        $noteForm = new OrderNoteInputForm();
        $proc = new FormProcessor();
        $proc->process($noteForm);
        if ($proc->getStatus() === IFormProcessor::STATUS_ERROR) $this->redirect("confirm", $proc->getMessage());
        if ($proc->getStatus() === IFormProcessor::STATUS_OK) {
            $cart->setNote((string)$noteForm->getInput("note")->getValue());
            $cart->store();
        }

        try {
            $processor = CheckoutContent::OrderProcessor();
            $processor->createOrder(Module::Active()->getUserID());
            $orderID = $processor->getOrderID();
        }
        catch (Exception $e) {
            $details = $e->getMessage() . " - " . $e->getTraceAsString();
            Debug::ErrorLog("Order failed: " . $details);
            if (!(new OrderErrorAdminMailer($details))->send()) {
                error_log("Unable to send the order error mail");
            }
            $this->redirect("confirm", tr("Възникна грешка при обработка на Вашата поръчка.") . " " . tr("Моля, опитайте отново или се свържете с нас."));
        }

        //mail failures are logged by Mailer::send() - the order is made
        try {
            (new OrderConfirmationMailer($orderID))->send();
        }
        catch (Exception $e) {
            error_log("Order $orderID: the client confirmation mail failed: " . $e->getMessage());
        }
        try {
            (new OrderConfirmationAdminMailer($orderID))->send();
        }
        catch (Exception $e) {
            error_log("Order $orderID: the shop confirmation mail failed: " . $e->getMessage());
        }

        Session::Set("checkout.orderID", $orderID);
        $this->redirect("complete");
    }
}
