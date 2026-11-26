<?php
include_once("mailers/Mailer.php");
include_once("store/forms/VoucherForm.php");

/**
 * A gift voucher request - to the shop - the mail template store/mails/voucher_admin.php
 */
class VoucherAdminMailer extends Mailer
{

    public function __construct(VoucherForm $form)
    {
        parent::__construct();

        $this->to = Spark::Get(StoreConfig::ORDER_EMAIL);
        $this->subject = tr("Gift voucher request at %s", Spark::Get(Config::SITE_DOMAIN));

        $data = array();
        foreach (array("rcpt_name", "name", "phone", "amount", "note") as $name) {
            $data[$name] = (string)$form->getInput($name)->getValue();
        }
        $this->useTemplate("voucher_admin", $data);
    }

}
