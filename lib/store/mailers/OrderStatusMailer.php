<?php
include_once("mailers/Mailer.php");
include_once("utils/url/URL.php");
include_once("utils/url/URLParameter.php");
include_once("store/beans/OrdersBean.php");
include_once("beans/UsersBean.php");

/**
 * The new status of an order - to the client - the mail template store/mails/order_status.php
 */
class OrderStatusMailer extends Mailer
{

    public function __construct(int $orderID, string $status)
    {
        parent::__construct();

        $orders = new OrdersBean();
        $order = $orders->getByID($orderID, "userID");

        $users = new UsersBean();
        $user = $users->getByID((int)$order["userID"], "fullname", "email");

        $this->to = (string)$user["email"];
        $this->subject = tr("Your order No %s at %s", $orderID, Spark::Get(Config::SITE_DOMAIN));

        $url = new URL(Spark::Get(Config::LOCAL) . "/account/orders/details");
        $url->add(new URLParameter("orderID", (string)$orderID));

        $this->useTemplate("order_status", array(
            "orderID" => $orderID,
            "status" => $status,
            "fullname" => (string)$user["fullname"],
            "account_url" => (string)$url->fullURL(),
        ));
    }

}
