<?php
include_once("beans/OrdersBean.php");
include_once("cart/PaymentResult.php");
enum PaymentStatus : int
{
    case WAITING_PAYMENT = 1;
    case PAYMENT_SUCCESS = 2;
    case PROCESSING_SHIPMENT = 3;
    case SHIPPED = 4;

}
abstract class PaymentProcessor
{
    protected string $gateway_used;
    protected int $userID;


    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    protected abstract function processOrderImpl(int $orderID, array $order_row);

    protected abstract function processTokenImpl(string $token);

    protected abstract function cancelTokenImpl(string $token);

    public function processOrder(int $orderID) : void
    {
        $order_row = PaymentProcessor::checkOrder($orderID, $this->userID);
        $result = $this->processOrderImpl($orderID, $order_row);
        $this->paymentFinal($result);

    }

    public function processToken(string $token) : void
    {
        $result = $this->processTokenImpl($token);
        $chk = get_class($result);
        if ($chk && strcmp($chk, "PaymentResult") == 0) {
            $this->paymentFinal($result);
        }
        else {
            throw new Exception("Undefined Error Processing payment");
        }

    }

    public function cancelToken(string $token) : void
    {
        $orderID = $this->cancelTokenImpl($token);
        header("Location: payment.php?orderID=$orderID");
        exit;
    }

    protected function paymentFinal(PaymentResult $payment_result) : void
    {

        $orderID = $payment_result->getOrderID();

        //TODO
        //$ob = new OrdersBean();
        //$ob->finalizePayment($payment_result);

        header("Location: confirmation.php?orderID=$orderID");
        exit;
    }



    //process payment using datacash
    //status = 1 awaiting payment, 2 payment processed fine, 3 shipment processing, 4 shipped
    public static function checkOrder(int $orderID, int $userID)
    {
        $ob = new OrdersBean();
        $order = $ob->getByID($orderID);
        $ownerID = $order["userID"];

        if ($ownerID !== $userID) {
            throw new Exception("Order owner miss-match");
        }

        $status = (int)$order["status"];
//        if ($status !== OrdersBean::STATUS_AWAITING_PAYMENT) {
//            throw new Exception("Incorrect order status.");
//        }
        return $order;
    }
}