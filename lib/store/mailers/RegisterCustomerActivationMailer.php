<?php
include_once("mailers/Mailer.php");
include_once("utils/url/URL.php");
include_once("templates/Module.php");
include_once("utils/url/URLParameter.php");

/**
 * The activation of a new client profile - the mail template store/features/shop/mails/register_activation.php
 */
class RegisterCustomerActivationMailer extends Mailer
{

    public function __construct(string $fullname, string $email, string $confirm_code)
    {
        parent::__construct();

        $this->to = $email;
        $this->subject = tr("Profile activation at %s", Spark::Get(Config::SITE_DOMAIN));

        $activationURL = Module::URL("account", "/activate") ?? new URL(Spark::Get(Config::LOCAL) . "/account/activate");
        $activationURL->add(new URLParameter("email", $email));
        $activationURL->add(new URLParameter("confirm_code", $confirm_code));
        $activationURL->add(new URLParameter("SubmitForm", "ActivateProfileInputForm"));

        $this->useTemplate("register_activation", array(
            "fullname" => $fullname,
            "link" => (string)$activationURL->fullURL(),
            "code" => $confirm_code,
        ));
    }

}
