<?php
include_once("mailers/Mailer.php");

class RegisterCustomerActivationMailer extends Mailer
{

    public function __construct(string $fullname, string $email, string $confirm_code)
    {
        parent::__construct();

        $this->to = $email;

        $this->subject = "Успешна Регистрация";

        $message = "Здравейте, $fullname\r\n\r\n";
        $message .= "Изпращаме Ви това съобщение за да Ви уведомим че, регистрацията Ви на " . Spark::Get(Config::SITE_DOMAIN) . " е завършена успешно.";
        $message .= "\r\n\r\n";

        $message .= "За да активирате Вашият профил проследете връзката за активация по долу.";
        $message .= "\r\n\r\n";

        $activationURL = new URL(Spark::Get(Config::LOCAL)."/account/activate.php");
        $activationURL->add(new URLParameter("email", $email));
        $activationURL->add(new URLParameter("confirm_code", $confirm_code));
        $activationURL->add(new URLParameter("SubmitForm", "ActivateProfileInputForm"));
        $message .= "<a href='{$activationURL->fullURL()}'>Активация на профил</a>";

        $message .= "\r\n\r\n";
        $message .= "\r\n\r\n";

        $message .= $activationURL->fullURL();

        $message .= "\r\n\r\n";
        $message .= "\r\n\r\n";

        $message .= "Код за активация на профил: $confirm_code";

        $message .= "\r\n\r\n";
        $message .= "\r\n\r\n";

        $message .= "Поздрави,\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $this->body = $this->templateMessage($message);

    }

}

?>
