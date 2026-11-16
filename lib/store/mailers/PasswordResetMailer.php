<?php
include_once("mailers/Mailer.php");

/**
 * The password reset link of a client
 */
class PasswordResetMailer extends Mailer
{

    public function __construct(string $email, URL $resetURL)
    {
        parent::__construct();

        $this->to = $email;
        $this->subject = tr("Смяна на парола") . " - " . Spark::Get(Config::SITE_DOMAIN);

        $link = $resetURL->fullURL();

        $message = tr("Здравейте") . ",\r\n\r\n";
        $message .= tr("Получихме заявка за смяна на паролата на Вашия профил в") . " " . Spark::Get(Config::SITE_DOMAIN) . ".\r\n\r\n";
        $message .= tr("За да зададете нова парола, отворете връзката по-долу. Връзката е валидна 1 час и може да се използва само веднъж.") . "\r\n\r\n";
        $message .= "<a href='" . Spark::AttributeValue((string)$link) . "'>" . tr("Смяна на парола") . "</a>\r\n\r\n";
        $message .= Spark::AttributeValue((string)$link) . "\r\n\r\n";
        $message .= tr("Ако не сте заявили смяна на паролата, просто игнорирайте това писмо - паролата Ви остава непроменена.") . "\r\n\r\n";
        $message .= tr("Поздрави") . ",\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $this->body = $message; //send() wraps it in the HTML document
    }

}
