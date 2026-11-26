<?php
include_once("mailers/Mailer.php");

/**
 * The password reset link of a client - the mail template store/mails/password_reset.php
 */
class PasswordResetMailer extends Mailer
{

    public function __construct(string $email, URL $resetURL)
    {
        parent::__construct();

        $this->to = $email;
        $this->subject = tr("Change password") . " - " . Spark::Get(Config::SITE_DOMAIN);

        $this->useTemplate("password_reset", array(
            "link" => (string)$resetURL->fullURL(),
            "site" => (string)Spark::Get(Config::SITE_DOMAIN),
        ));
    }

}
