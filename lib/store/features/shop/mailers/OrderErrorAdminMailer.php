<?php
include_once("mailers/Mailer.php");

/**
 * An order could not be finalized - to the shop - the mail template store/features/shop/mails/order_error_admin.php
 */
class OrderErrorAdminMailer extends Mailer
{

    public function __construct(string $error_text)
    {
        parent::__construct();

        //not set in config/store.php - the shop's order address
        $this->to = Spark::Get(StoreConfig::ORDER_ERROR_EMAIL) ?: Spark::Get(StoreConfig::ORDER_EMAIL);
        $this->subject = tr("Order error at %s", Spark::Get(Config::SITE_DOMAIN));
        $this->useTemplate("order_error_admin", array("error" => $error_text));
    }

}
