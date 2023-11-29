<?php
include_once("mailers/Mailer.php");
include_once("beans/UsersBean.php");

class QueryProductMailer extends Mailer
{

    protected $client_email = "";
    protected $client_name = "";
    protected $query_text = "";
    protected $product_name = "";
    protected $product_link = "";

    public function setClient(string $client_email, string $client_name)
    {
        $this->client_email = $client_email;
        $this->client_name = $client_name;
    }

    public function setQueryText(string $message)
    {
        $this->query_text = $message;
    }

    public function setProduct(string $product_name, string $product_link)
    {
        $this->product_name = $product_name;
        $this->product_link = $product_link;
    }

    public function __construct()
    {

        parent::__construct();

        $this->to = ORDER_EMAIL;

        $this->subject = "Запитване за продукт на: ".SITE_DOMAIN;

    }

    public function prepareMessage()
    {
        debug ("Preparing message contents ...");

        $message  = "От: ".$this->client_name;
        $message .= "\r\n";
        $message .= "Email: ".$this->client_email;
        $message .= "\r\n";
        $message .= "Запитване: ";
        $message .= "\r\n\r\n";
        $message .= $this->query_text;
        $message .= "\r\n\r\n";

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
