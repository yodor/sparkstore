<?php
include_once("cart/PaymentProcessor.php");
include_once("Authenticator.php");
include_once("store/utils/cart/payment/PaymentProcessor.php");
include_once("store/utils/cart/payment/PaymentResult.php");

class FreeOrderProcessor extends PaymentProcessor
{

    protected function processOrderImpl($orderID, $order_row)
    {

        $transaction_time = time();
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