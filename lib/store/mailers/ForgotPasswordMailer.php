<?php
include_once("mailers/Mailer.php");

class ForgotPasswordMailer extends Mailer
{

    public function __construct(string $email, string $random_pass, URL $loginURL)
    {
        parent::__construct();

        $this->to = $email;

        $this->subject = "Забравена Парола / Forgot Password Request";

        $message = "Здравейте, <br><br>\r\n\r\n";

        $message .= "Изпращаме Ви това съобщение във връзка с Вашата заявка за подновяване на паролата на " . Spark::Get(Config::SITE_DOMAIN);

        $message .= "\r\n\r\n<br><br>";

        $message .= "Може да ползвате следните име и парола за достъп: ";
        $message .= "<br><br>\r\n\r\n";
        $message .= "Email: " . $email;
        $message .= "<br>\r\n";
        $message .= "Password: " . $random_pass;

        $message .= "<br><br>\r\n\r\n";

        $message .= "Кликнете <a href='{$loginURL->fullURL()}'>Тук</a> за вход или въведете слдения URL във Вашият браузър: ";
        $message .= $loginURL->fullURL();

        $message .= "<br><br>\r\n\r\n";

        $message .= "<BR><BR>\r\n\r\nС Уважение,<BR>\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $message .= "<BR><BR>\r\n\r\n";

        $message .= "Hello, <br><br>\r\n\r\n";

        $message .= "This message is sent in relation to your forgot password request at " . Spark::Get(Config::SITE_DOMAIN);

        $message .= "\r\n\r\n<br><br>";

        $message .= "You can use the following to access your details: ";
        $message .= "<br><br>\r\n\r\n";
        $message .= "Username: " . $email;
        $message .= "<br>\r\n";
        $message .= "Password: " . $random_pass;

        $message .= "<br><br>\r\n\r\n";

        $message .= "Click <a href='{$loginURL->fullURL()}'>Here</a> to login or open this URL in your browser window: ";
        $message .= $loginURL->fullURL();

        $message .= "<br><br>\r\n\r\n";

        $message .= "<BR><BR>\r\n\r\nSincerely,<BR>\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $this->body = $this->templateMessage($message);

    }

}