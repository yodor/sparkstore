<?php
include_once("mailers/Mailer.php");

class RegisterCustomerPasswordMailer extends Mailer
{

    public function __construct($userID, $random_pass, $fullname, $email)
    {
        parent::__construct();

        $this->to = $email;

        $this->subject = "Успешна Регистрация / Registration Complete";

        $message = "Здравейте, $fullname<br><br>\r\n\r\n";
        $message .= "Изпращаме Ви това съобщение за да Ви уведомим че, регистрацията Ви на " . Spark::Get(Config::SITE_DOMAIN) . " е завършена успешно.";
        $message .= "\r\n\r\n<br><br>";
        $message .= "Моля използвайте потребителското име и парола по долу за достъп до страниците за регистрирани клиенти: ";
        $message .= "<br><br>\r\n\r\n";
        $message .= "Username: " . $email;
        $message .= "<br>\r\n";
        $message .= "Password: " . $random_pass;
        $message .= "<br><br>\r\n\r\n";
        $accountURL = new URL(Spark::Get(Config::LOCAL)."/account/");
        $message .= "Натиснете <a href='{$accountURL->fullURL()}'>Тук</a> за достъп до страниците за регистрирани клиенти или отворете този URL: ";
        $message .= $accountURL->fullURL();

        $message .= "<br><br>\r\n\r\n";

        $message .= "<BR><BR>\r\n\r\nС Уважение,<BR>\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $this->body = $this->templateMessage($message);

    }

}

?>
