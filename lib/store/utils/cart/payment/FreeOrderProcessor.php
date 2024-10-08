<?php
include_once("cart/PaymentProcessor.php");
include_once("Authenticator.php");

class FreeOrderProcessor extends PaymentProcessor
{

    protected function processOrderImpl($orderID, $order_row)
    {
        $db = DBConnections::Open();
        $transaction_time = $db->dateTime();
        //
        $reference = Authenticator::RandomToken(16);

        return new PaymentResult($orderID, $reference, $transaction_time, "free_order");
    }

    protected function processTokenImpl($token)
    {
        throw new Exception("Not implemented");
    }

    protected function cancelTokenImpl($token)
    {
        throw new Exception("Not implemented");
    }
}