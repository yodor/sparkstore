<?php
include_once("mailers/Mailer.php");
include_once("store/mailers/OrderMailData.php");

/**
 * A new order - to the shop (StoreConfig::ORDER_EMAIL) - the mail template store/mails/order_confirmation_admin.php
 */
class OrderConfirmationAdminMailer extends Mailer
{

    public function __construct(int $orderID)
    {
        parent::__construct();

        $data = OrderMailData::Load($orderID);
        $this->to = (string)Spark::Get(StoreConfig::ORDER_EMAIL);
        $this->subject = tr("New order No %s at %s", $orderID, Spark::Get(Config::SITE_DOMAIN));
        //a reply goes to the client
        if ((string)$data["client"]["email"] !== "") $this->setReplyTo((string)$data["client"]["email"], (string)$data["client"]["fullname"]);
        $this->useTemplate("order_confirmation_admin", $data);
    }

}
