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
        $message .= "Изпращаме Ви това съобщение за да Ви уведомим че, регистрацията Ви на " . SITE_DOMAIN . " е завършена успешно.";
        $message .= "\r\n\r\n";

        $message .= "За да активирате Вашият профил проследете връзката за активация по долу.";
        $message .= "\r\n\r\n";

        $activation_url = SITE_URL.LOCAL."/account/activate.php?email=$email&confirm_code=$confirm_code&SubmitForm=ActivateProfileInputForm";

        $message .= "<a href='$activation_url'>Активация на профил</a>";

        $message .= "\r\n\r\n";
        $message .= "\r\n\r\n";

        $message .= $activation_url;

        $message .= "\r\n\r\n";
        $message .= "\r\n\r\n";

        $message .= "Код за активация на профил: $confirm_code";

        $message .= "\r\n\r\n";
        $message .= "\r\n\r\n";

        $message .= "Поздрави,\r\n";
        $message .= SITE_DOMAIN;

        $this->body = $this->templateMessage($message);

    }

}

?>
