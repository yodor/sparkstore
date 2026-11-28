<?php
/**
 * The store mails for the admin preview (MailPreviews) - the first product of the store, or samples. The mails of the
 * store features are in their own previews.php (features/shop, features/fast_order).
 */
include_once("store/mailers/InstockProductMailer.php");
include_once("store/mailers/StoreMailSamples.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/url/ProductURL.php");
include_once("mailers/MailPreviews.php");

//the enquiries of a store site go to the shop's address (StoreContactFormResponder, QueryProductFormResponder)
MailPreviews::SetAddress("enquiries", tr("Enquiries (the contact form, the product query)"), (string)Spark::Get(StoreConfig::ORDER_EMAIL),
    tr("Content > Config > Base: 'Email (For receiving orders)', else config/store.php: ORDER_EMAIL"));

return array(
    "instock" => array(tr("In stock again - to the client"), function () : Mailer {
        $prodID = StoreMailSamples::FirstProduct();
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
);
