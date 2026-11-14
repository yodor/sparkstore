<?php
include_once("responders/RequestResponder.php");
include_once("store/mailers/OrderConfirmationMailer.php");

/**
 * Sends the order confirmation e-mail of an order to its client again
 */
class OrderConfirmationRequestResponder extends RequestResponder
{
    protected int $orderID = -1;

    public function getParameterNames(): array
    {
        $result = parent::getParameterNames();
        $result[] = "orderID";
        return $result;
    }

    protected function parseParams() : void
    {
        if (!$this->url->contains("orderID")) throw new Exception("Order ID not passed");
        $this->orderID = (int)$this->url->get("orderID")->value();
    }

    protected function processImpl() : void
    {
        $mailer = new OrderConfirmationMailer($this->orderID);
        if (!$mailer->send()) throw new Exception(tr("The e-mail could not be sent"));
        Session::SetAlert(tr("Order confirmation e-mail was sent"));
    }
}
