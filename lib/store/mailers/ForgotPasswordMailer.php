<?php
include_once("mailers/Mailer.php");
include_once("beans/UsersBean.php");

class ForgotPasswordMailer extends Mailer
{

    public function __construct(string $email, string $random_pass)
    {

        $users = new UsersBean();
        $userID = $users->email2id($email);
        $user_row = $users->getByID($userID);

        $this->to = $user_row["email"];

        $this->subject = "Забравена Парола / Forgot Password Request";

        $message = "Здравейте, <br><br>\r\n\r\n";

        $message .= "Изпращаме Ви това съобщение във връзка с Вашата заявка за подновяване на паролата на " . SITE_DOMAIN;

        $message .= "\r\n\r\n<br><br>";

        $message .= "Може да ползвате следните име и парола за достъп: ";
        $message .= "<br><br>\r\n\r\n";
        $message .= "Username: " . $user_row["email"];
        $message .= "<br>\r\n";
        $message .= "Password: " . $random_pass;

        $message .= "<br><br>\r\n\r\n";

        $message .= "Натиснете <a href='" . SITE_URL . LOCAL . "/account/login.php'>Тук</a> за вход или въведете слдения URL във Вашият браузър: ";
        $message .= SITE_URL . LOCAL . "/account/login.php";

        $message .= "<br><br>\r\n\r\n";

        $message .= "<BR><BR>\r\n\r\nС Уважение,<BR>\r\n";
        $message .= SITE_DOMAIN;

        $message .= "<BR><BR>\r\n\r\n";

        $message .= "Hello, <br><br>\r\n\r\n";

        $message .= "This message is sent in relation to your forgot password request at " . SITE_DOMAIN;

        $message .= "\r\n\r\n<br><br>";

        $message .= "You can use the following to access your details: ";
        $message .= "<br><br>\r\n\r\n";
        $message .= "Username: " . $user_row["email"];
        $message .= "<br>\r\n";
        $message .= "Password: " . $random_pass;

        $message .= "<br><br>\r\n\r\n";

        $message .= "Click <a href='" . SITE_URL . LOCAL . "/account/login.php'>Here</a> login or open this URL in your browser window: ";
        $message .= SITE_URL . LOCAL . "/account/login.php";

        $message .= "<br><br>\r\n\r\n";

        $message .= "<BR><BR>\r\n\r\nSincerely,<BR>\r\n";
        $message .= SITE_DOMAIN;

        $this->body = $this->templateMessage($message);

    }

}

?>
