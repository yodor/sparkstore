<?php
/**
 * The store mails for the admin preview (MailPreviews) - the last order and the first product of the store, or samples
 */
include_once("store/mailers/OrderConfirmationMailer.php");
include_once("store/mailers/OrderConfirmationAdminMailer.php");
include_once("store/mailers/OrderStatusMailer.php");
include_once("store/mailers/OrderErrorAdminMailer.php");
include_once("store/mailers/InstockProductMailer.php");
include_once("store/mailers/RegisterCustomerActivationMailer.php");
include_once("store/mailers/PasswordResetMailer.php");
include_once("store/mailers/VoucherAdminMailer.php");
include_once("store/mailers/FastOrderAdminMailer.php");
include_once("store/forms/VoucherForm.php");
include_once("store/forms/FastOrderProductForm.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/url/ProductURL.php");
include_once("store/beans/OrdersBean.php");
include_once("store/beans/SellableProducts.php");

/**
 * The ID of the last order - an exception without orders
 */
$lastOrder = function () : int {
    $orders = new OrdersBean();
    $query = $orders->query($orders->key());
    $query->stmt->order($orders->key(), OrderDirection::DESC);
    $query->stmt->limit(1);
    $query->exec();
    $row = $query->next();
    $query->free();
    if (!is_array($row)) throw new Exception(tr("There are no orders yet"));
    return (int)$row[$orders->key()];
};

/**
 * The ID of a visible product - an exception without products
 */
$firstProduct = function () : int {
    $products = new SellableProducts();
    $query = $products->query("prodID");
    $query->stmt->order("prodID", OrderDirection::ASC);
    $query->stmt->limit(1);
    $query->exec();
    $row = $query->next();
    $query->free();
    if (!is_array($row)) throw new Exception(tr("There are no products yet"));
    return (int)$row["prodID"];
};

return array(
    "order_confirmation" => array(tr("Order confirmation - to the client (the last order)"), function () use ($lastOrder) : Mailer {
        return new OrderConfirmationMailer($lastOrder());
    }),
    "order_confirmation_admin" => array(tr("New order - to the shop (the last order)"), function () use ($lastOrder) : Mailer {
        return new OrderConfirmationAdminMailer($lastOrder());
    }),
    "order_status" => array(tr("Order status - to the client (the last order)"), function () use ($lastOrder) : Mailer {
        return new OrderStatusMailer($lastOrder(), OrdersBean::STATUS_SENT);
    }),
    "fast_order_admin" => array(tr("Fast order - to the shop"), function () use ($firstProduct) : Mailer {
        $form = new FastOrderProductForm();
        $form->getInput("fullname")->setValue("Ivan Petrov");
        $form->getInput("phone")->setValue("0888 123 456");
        return new FastOrderAdminMailer($form, SellableItem::Load($firstProduct()));
    }),
    "voucher_admin" => array(tr("Gift voucher request - to the shop"), function () : Mailer {
        $form = new VoucherForm();
        foreach (array("rcpt_name" => "Maria", "name" => "Ivan Petrov", "phone" => "0888 123 456", "amount" => "50", "note" => "Sample note") as $name => $value) {
            $form->getInput($name)->setValue($value);
        }
        return new VoucherAdminMailer($form);
    }),
    "register_activation" => array(tr("Profile activation - to the client"), function () : Mailer {
        return new RegisterCustomerActivationMailer("Ivan Petrov", "client@example.com", "a1b2c3d4e5");
    }),
    "password_reset" => array(tr("Password reset - to the client"), function () : Mailer {
        return new PasswordResetMailer("client@example.com", new URL(Spark::Get(Config::LOCAL) . "/account/password/reset?token=sample"));
    }),
    "instock" => array(tr("In stock again - to the client"), function () use ($firstProduct) : Mailer {
        $prodID = $firstProduct();
        $item = SellableItem::Load($prodID);
        $url = new ProductURL();
        $url->setProductID($prodID);
        $url->setProductName($item->getTitle());
        $mailer = new InstockProductMailer();
        $mailer->setRecipient("client@example.com");
        $mailer->setProduct($item->getTitle(), $url);
        $mailer->prepareMessage();
        return $mailer;
    }),
    "order_error_admin" => array(tr("Order error - to the shop"), function () : Mailer {
        return new OrderErrorAdminMailer("Sample error text");
    }),
);
