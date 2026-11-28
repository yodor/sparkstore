<?php
/**
 * The mail of the fast order for the admin preview (MailPreviews) - the first product of the store
 */
include_once("store/features/fast_order/mailers/FastOrderAdminMailer.php");
include_once("store/mailers/StoreMailSamples.php");
include_once("store/features/fast_order/forms/FastOrderProductForm.php");
include_once("store/utils/SellableItem.php");

return array(
    "fast_order_admin" => array(tr("Fast order - to the shop"), function () : Mailer {
        $form = new FastOrderProductForm();
        $form->getInput("fullname")->setValue("Ivan Petrov");
        $form->getInput("phone")->setValue("0888 123 456");
        return new FastOrderAdminMailer($form, SellableItem::Load(StoreMailSamples::FirstProduct()));
    }),
);
