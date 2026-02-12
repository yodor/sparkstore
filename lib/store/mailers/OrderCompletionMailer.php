<?php
include_once("mailers/Mailer.php");
include_once("store/beans/OrdersBean.php");

class OrderCompletionMailer extends Mailer
{

    public function __construct($orderID)
    {
        parent::__construct();

        $orders = new OrdersBean();
        $order_row = $orders->getByID($orderID);

        $this->to = $order_row["client_identifier"];

        $this->subject = "Order Status - OrderID $orderID";

        $message = "Здравейте, <br><br>\r\n\r\n";

        $message .= "Изпращаме Ви това съобщение за да Ви уведомим че Вашата поръчка от " . Spark::Get(Config::SITE_DOMAIN) . " беше изпратена. ";

        $message .= "\r\n\r\n<br><br>";
        $message .= "Поръчка Номер: $orderID\r\n<br>";
        $message .= "Дата: " . $order_row["order_date"] . "\r\n<br>";
        $message .= "Код на поръчката: " . $order_row["order_identifier"] . "\r\n<br>";

        $message .= "Съдържание на поръчката:<br>\r\n";
        $message .= $order_row["cart_data"] . "<br>\r\n";
        $message .= "\r\n\r\n<br><br>";

        $message .= "Цена Всичко: " . $order_row["order_total"] . " " . $order_row["active_currency"];
        if (Spark::GetFloat(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $message .= "Цена Всичко: " . formatPrice(($order_row["order_total"] / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE)), Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY));
        }

        $message .= "<BR><BR>\r\n\r\nС Уважение,<BR>\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $message .= "<BR><BR>\r\n\r\n";

        $message .= "Hello, <br><br>\r\n\r\n";

        $message .= "This message is sent to let you know that your order at " . Spark::Get(Config::SITE_DOMAIN) . " is now shipped. ";

        $message .= "\r\n\r\n<br><br>";
        $message .= "OrderID: $orderID\r\n<br>";
        $message .= "Date: " . $order_row["order_date"] . "\r\n<br>";
        $message .= "Confirmation Ticket: " . $order_row["order_identifier"] . "\r\n<br>";

        $message .= "Products Ordered:<br>\r\n";
        $message .= $order_row["cart_data"] . "<br>\r\n";
        $message .= "\r\n\r\n<br><br>";

        $message .= "Order Total: " . $order_row["order_total"] . " " . $order_row["active_currency"];
        if (Spark::GetFloat(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $message .= "Order Total: " . formatPrice($order_row["order_total"] / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE), Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY));
        }
        $message .= "<BR><BR>\r\n\r\nSincerely,<BR>\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $this->body = $this->templateMessage($message);

    }

}