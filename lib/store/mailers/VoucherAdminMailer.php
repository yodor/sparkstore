<?php
include_once("mailers/Mailer.php");
include_once("store/forms/VoucherForm.php");


class VoucherAdminMailer extends Mailer
{

    public function __construct(VoucherForm $form)
    {

        parent::__construct();


        debug ("Preparing message ...");

        $this->to = ORDER_ADMIN_EMAIL;
        $this->subject = "Поръчка на ваучер на ".SITE_DOMAIN;

        $message = "Здравейте, \r\n\r\n";
        $message .= "Беше направена заявка за ваучер на ". SITE_DOMAIN;
        $message .= "\r\n\r\n";

        $message .= "Име на получателя: ".$form->getInput("rcpt_name")->getValue();
        $message .= "\r\n";
        $message .= "Заявител име: ".$form->getInput("name")->getValue();
        $message .= "\r\n";
        $message .= "Заявител телефон: ".$form->getInput("phone")->getValue();
        $message .= "\r\n";
        $message .= "Сума: ".$form->getInput("amount")->getValue();
        $message .= "\r\n";
        $message .= "Забележка: ".$form->getInput("note")->getValue();
        $message .= "\r\n";

        $message .= "\r\n\r\n";


        $this->body = $this->templateMessage($message);

        debug ("Message contents prepared ...");

    }

}

?>
