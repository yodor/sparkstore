<?php
include_once("mailers/Mailer.php");
include_once("beans/ConfigBean.php");

class ContactRequestMailer extends Mailer
{
    public function __construct()
    {
        parent::__construct();

//        $config = ConfigBean::Factory();
//        $config->setSection("global");

        $this->subject = tr("Получена нова заявка за контакти");
        $this->to = ORDER_ADMIN_EMAIL;

        $message = "";
        $message .= tr("Здравейте").",\r\n";
        $message .= "\r\n";
        $message .= tr("Заявка за контакти беше получена на - ") . SITE_DOMAIN . "\r\n";
        $message .= "\r\n";
        $message .= "\r\n";
        $message .= tr("За достъп до заявките натиснете връзката по долу").": \r\n";
        $message.= "<a href='" . SITE_URL . ADMIN_LOCAL . "/contact_requests/list.php'>".tr("Списък заявки")."</a>";
        $message .= "\r\n";

        $message .= tr("Поздрави").",\r\n";
        $message .= "\r\n";
        $message .= SITE_DOMAIN;

        $this->body = $this->templateMessage($message);

    }
}

?>