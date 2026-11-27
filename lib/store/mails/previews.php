<?php
/**
 * The store mails for the admin preview (MailPreviews) - the first product of the store, or samples. The mails of the
 * store features are in their own previews.php (features/shop, features/fast_order).
 */
include_once("store/mailers/InstockProductMailer.php");
include_once("store/mailers/StoreMailSamples.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/url/ProductURL.php");

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
