<?php
include_once("mailers/Mailer.php");

class OrderConfirmationAdminMailer extends Mailer
{

    public function __construct(int $orderID)
    {
        parent::__construct();

        $this->to = ORDER_ADMIN_EMAIL;

        $this->subject = "Нова поръчка - OrderID:$orderID";

        $message = "Нова поръчка беше направена на " . SITE_DOMAIN;
        $message .= "\r\n\r\n";
        $message .= "OrderID: $orderID";
        $message .= "\r\n\r\n";

        $message .= "\r\n\r\n";
        $order_link = SITE_URL . LOCAL . "/admin/orders/active.php?orderID=$orderID";
        $message .= "Кликнете по долу за да видите поръчката.";
        $message .= "\r\n\r\n";

        $message .= "<a href='$order_link'>Списък поръчки</a>";

        $this->body = $this->templateMessage($message);

    }

}

?>
