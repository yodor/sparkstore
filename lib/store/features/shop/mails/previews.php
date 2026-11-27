<?php
/**
 * The mails of the shop for the admin preview (MailPreviews) - the last order of the store, or samples
 */
include_once("store/mailers/OrderConfirmationMailer.php");
include_once("store/mailers/OrderConfirmationAdminMailer.php");
include_once("store/mailers/OrderStatusMailer.php");
include_once("store/mailers/OrderErrorAdminMailer.php");
include_once("store/mailers/RegisterCustomerActivationMailer.php");
include_once("store/mailers/PasswordResetMailer.php");
include_once("store/mailers/StoreMailSamples.php");
include_once("store/beans/OrdersBean.php");
include_once("templates/Module.php");
include_once("utils/url/URLParameter.php");

return array(
    "order_confirmation" => array(tr("Order confirmation - to the client (the last order)"), function () : Mailer {
        return new OrderConfirmationMailer(StoreMailSamples::LastOrder());
    }),
    "order_confirmation_admin" => array(tr("New order - to the shop (the last order)"), function () : Mailer {
        return new OrderConfirmationAdminMailer(StoreMailSamples::LastOrder());
    }),
    "order_status" => array(tr("Order status - to the client (the last order)"), function () : Mailer {
        return new OrderStatusMailer(StoreMailSamples::LastOrder(), OrdersBean::STATUS_SENT);
    }),
    "register_activation" => array(tr("Profile activation - to the client"), function () : Mailer {
        return new RegisterCustomerActivationMailer("Ivan Petrov", "client@example.com", "a1b2c3d4e5");
    }),
    "password_reset" => array(tr("Password reset - to the client"), function () : Mailer {
        $url = Module::URL("account", "/password/reset") ?? new URL(Spark::Get(Config::LOCAL) . "/account/password/reset");
        $url->add(new URLParameter("token", "sample"));
        return new PasswordResetMailer("client@example.com", $url);
    }),
    "order_error_admin" => array(tr("Order error - to the shop"), function () : Mailer {
        return new OrderErrorAdminMailer("Sample error text");
    }),
);
