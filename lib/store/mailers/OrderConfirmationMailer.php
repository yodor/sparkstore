<?php
include_once("mailers/Mailer.php");
include_once("store/mailers/OrderMailData.php");

/**
 * The order confirmation to the client - the mail template store/features/shop/mails/order_confirmation.php
 */
class OrderConfirmationMailer extends Mailer
{

    public function __construct(int $orderID)
    {
        parent::__construct();

        $data = OrderMailData::Load($orderID);
        $this->to = (string)$data["client"]["email"];
        $this->subject = tr("Order No %s at %s", $orderID, Spark::Get(Config::SITE_DOMAIN));
        $this->useTemplate("order_confirmation", $data);
    }

}
