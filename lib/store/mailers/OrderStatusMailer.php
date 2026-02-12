<?php
include_once("mailers/Mailer.php");
include_once("store/beans/OrdersBean.php");
include_once("beans/UsersBean.php");

class OrderStatusMailer extends Mailer
{

    public function __construct(int $orderID, string $status)
    {
        parent::__construct();

        $orders = new OrdersBean();
        $order = $orders->getByID($orderID, "userID");

        $userID = (int)$order["userID"];

        $users = new UsersBean();
        $user = $users->getByID($userID, "userID", "fullname", "email", "phone");

        $this->to = $user["email"];

        $this->subject = "Вашата поръчка от ".Spark::Get(Config::SITE_DOMAIN);

        $message = "Здравейте, {$user["fullname"]}\r\n\r\n";

        $message .= "Състоянието на Вашата поръчка от " . Spark::Get(Config::SITE_DOMAIN) . " беше обновено: ";

        $message .= tr($status) . "\r\n";
        $message .= "\r\n\r\n";

        $message .= "\r\n\r\n";

        $message .= "Поздрави,";
        $message .= "\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);


        $this->body = $this->templateMessage($message);

    }

}