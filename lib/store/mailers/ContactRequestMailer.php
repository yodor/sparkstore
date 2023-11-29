<?php
include_once("mailers/Mailer.php");

class ContactRequestMailer extends Mailer
{

    protected $client_name = "";
    protected $query_text = "";

    public function setClient(string $client_name)
    {
        $this->client_name = $client_name;
    }

    public function setQueryText(string $message)
    {
        $this->query_text = $message;
    }

    public function __construct()
    {
        parent::__construct();

        $this->subject = "Получена нова заявка за контакти на : ".SITE_DOMAIN;
        $this->to = ORDER_EMAIL;
    }

    public function prepareMessage()
    {
        debug ("Preparing contact request message contents ...");

        $message  = "От: ".$this->client_name;
        $message .= "\r\n";
        $message .= "Запитване: ";
        $message .= "\r\n\r\n";
        $message .= $this->query_text;
        $message .= "\r\n\r\n";

        $message .= "За достъп до заявките натиснете връзката по долу";
        $message .= "\r\n";
        $message .= "<a href='" . SITE_URL . ADMIN_LOCAL . "/contact_requests/list.php'>".tr("Списък заявки")."</a>";
        $message .= "\r\n";

        $message .= "Поздрави,\r\n";
        $message .= SITE_DOMAIN;

        $this->body = $this->templateMessage($message);

        debug ("Contact request message contents prepared ...");
    }
}

?>
