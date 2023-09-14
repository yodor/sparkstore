<?php
include_once("mailers/Mailer.php");
include_once("store/forms/FastOrderProductForm.php");
include_once("store/utils/SellableItem.php");


class FastOrderAdminMailer extends Mailer
{

    public function __construct(FastOrderProductForm $form, SellableItem $item)
    {

        parent::__construct();


        debug ("Preparing message ...");

        $this->to = ORDER_ADMIN_EMAIL;
        $this->subject = "Бърза поръчка на ".SITE_DOMAIN;

        $message = "Здравейте, \r\n\r\n";
        $message .= "Беше направена бърза поръчка на ". SITE_DOMAIN;
        $message .= "\r\n\r\n";

        $message .= "Име: ".$form->getInput("fullname")->getValue();
        $message .= "\r\n";
        $message .= "Телефон: ".$form->getInput("phone")->getValue();
        $message .= "\r\n";
        $message .= "Адрес: ".$form->getInput("address")->getValue();
        $message .= "\r\n";

        $message .= "Поръчани продукти:\r\n\r\n";

        $message .= "<table border=1>";

        $message .= "<tr>";
        $message .= "<td>";
        $si = $item->getMainPhoto();
        $src = fullURL($si->hrefImage(256,256));
        $message .= "<img src='$src'>";
        $message .= "</td>";

        $message .= "<td>";

        $message .= "Продукт: ".$item->getTitle();
        $message .= "\r\n";
        $message .= "Цена: ".$item->getPriceInfo()->getSellPrice();
        $message .= "\r\n";

        if ($item->variantsCount()>0) {
            $options = $item->getVariantNames();
            foreach ($options as $idx=>$option_name) {
                $vitem = $item->getVariant($option_name);
                $message .= $vitem->getName().": ".$vitem->getSelected();
                $message .= "\r\n";
            }
        }

        $href = fullURL(LOCAL."/products/details.php?prodID=".$item->getProductID());
        $message .= "<a href='$href'>Виж продукта</a>";
        $message .= "\r\n";
        $message .= "</td>";
        $message .= "</tr>";

        $message .= "</table>";


        $message .= "\r\n\r\n";


        $this->body = $this->templateMessage($message);

        debug ("Message contents prepared ...");


    }

}

?>
