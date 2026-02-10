<?php
include_once("mailers/Mailer.php");
include_once("store/utils/url/ProductURL.php");

class InstockProductMailer extends Mailer
{

    protected string $product_name = "";
    protected ?ProductURL $product_link = null;


    public function __construct()
    {
        parent::__construct();
        $this->subject = "Отново в наличност на ".Spark::Get(Config::SITE_DOMAIN);
    }

    public function setRecipient(string $client_email) : void
    {
        $this->to = $client_email;
    }

    public function setProduct(string $product_name, ProductURL $product_link) : void
    {
        $this->product_name = $product_name;
        $this->product_link = $product_link;
    }

    public function prepareMessage() : void
    {

        if (is_null($this->product_link) || !$this->product_name || !$this->to) {
            throw new Exception("Object initialization incomplete.");
        }

        Debug::ErrorLog ("Preparing message contents ...");

        $message  = "Здравейте, ";
        $message .= "\r\n";
        $message .= "Търсеният от Вас продукт е отново в наличност при нас.";
        $message .= "\r\n";

        $message .= "Продукт: ";
        $message .= "\r\n";
        $message .= "<a href='{$this->product_link->fullURL()}'>{$this->product_name}</a>";
        $message .= "\r\n";
        $message .= "\r\n";

        $message .= "Поздрави,\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $this->body = $this->templateMessage($message);

        Debug::ErrorLog ("Message contents prepared ...");
    }

}

?>
