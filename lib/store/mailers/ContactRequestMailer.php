<?php
include_once("mailers/Mailer.php");

class ContactRequestMailer extends Mailer
{

    protected $client_name = "";
    protected $client_email = "";
    protected $query_text = "";

    public function setClient(string $client_name) : void
    {
        $this->client_name = $client_name;
    }

    public function setEmail(string $client_email) : void
    {
        $this->client_email = $client_email;
    }

    public function setQueryText(string $message)
    {
        $this->query_text = $message;
    }

    public function __construct()
    {
        parent::__construct();

        $this->subject = "Получена нова заявка за контакти на : ".Spark::Get(Config::SITE_DOMAIN);
        $this->to = Spark::Get(StoreConfig::ORDER_EMAIL);
    }

    public function prepareMessage()
    {
        Debug::ErrorLog ("Preparing contact request message contents ...");

        $message  = "От: ".$this->client_name;
        $message .= "\r\n";
        $message .= "Email: ".$this->client_email;
        $message .= "\r\n";
        $message .= "Запитване: ";
        $message .= "\r\n\r\n";
        $message .= $this->query_text;
        $message .= "\r\n\r\n";

        $message .= "За достъп до заявките натиснете връзката по долу";
        $message .= "\r\n";
        $contactsURL = new URL(Spark::Get(Config::ADMIN_LOCAL) . "/contact_requests/list.php");
        $message .= "<a href='{$contactsURL->fullURL()}'>".tr("Списък заявки")."</a>";
        $message .= "\r\n";

        $message .= "Поздрави,\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $this->body = $this->templateMessage($message);

        Debug::ErrorLog ("Contact request message contents prepared ...");
    }
}

?>
