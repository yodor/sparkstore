<?php
include_once("mailers/Mailer.php");

class InstockProductMailer extends Mailer
{

    protected string $product_name = "";
    protected string $product_link = "";


    public function __construct()
    {
        parent::__construct();
        $this->subject = "Отново в наличност на ".SITE_DOMAIN;
    }

    public function setRecipient(string $client_email)
    {
        $this->to = $client_email;
    }

    public function setProduct(string $product_name, string $product_link)
    {
        $this->product_name = $product_name;
        $this->product_link = $product_link;
    }

    public function prepareMessage()
    {

        debug ("Preparing message contents ...");

        $message  = "Здравейте, ";
        $message .= "\r\n";
        $message .= "Търсеният от Вас продукт е отново в наличност при нас.";
        $message .= "\r\n";

        $message .= "Продукт: ";
        $message .= "\r\n";
        $message .= "<a href='{$this->product_link}'>{$this->product_name}</a>";
        $message .= "\r\n";
        $message .= "\r\n";

        $message .= "Поздрави,\r\n";
        $message .= SITE_DOMAIN;

        $this->body = $this->templateMessage($message);

        debug ("Message contents prepared ...");
    }

}

?>
