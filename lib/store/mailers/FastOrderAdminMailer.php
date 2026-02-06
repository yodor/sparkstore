<?php
include_once("mailers/Mailer.php");
include_once("store/forms/FastOrderProductForm.php");
include_once("store/utils/SellableItem.php");


class FastOrderAdminMailer extends Mailer
{

    /**
     * Used to mail the fast order to admin
     * If SellableItem $item is not null and $form is FastOrderProductForm mail single item
     * If $form is ClientAddressInputForm mail all items in the cart
     * @param InputForm $form
     * @param SellableItem|null $item
     * @throws Exception
     */
    public function __construct(InputForm $form, ?SellableItem $item=null)
    {

        parent::__construct();

        Debug::ErrorLog ("Preparing message ...");

        $this->to = Spark::Get(StoreConfig::ORDER_EMAIL);
        $this->subject = "Бърза поръчка на ".Spark::Get(Config::SITE_DOMAIN);

        $message = "Здравейте, \r\n\r\n";
        $message .= "Беше направена бърза поръчка на ". Spark::Get(Config::SITE_DOMAIN);
        $message .= "\r\n\r\n";

        $message .= "Име: ".$form->getInput("fullname")->getValue();
        $message .= "\r\n";
        $message .= "Телефон: ".$form->getInput("phone")->getValue();
        $message .= "\r\n";
        if ($form->haveInput("address")) {
            $message .= "Адрес: " . $form->getInput("address")->getValue();
            $message .= "\r\n";
        }

        $message .= "Поръчани продукти:\r\n\r\n";


        $message .= "<table border=1>";

        //fast order cart items
        if ($form instanceof ClientAddressInputForm) {
            $cart = Cart::Instance();
            if ($cart->itemsCount()<1) throw new Exception(tr("Your shopping cart is empty"));
            $items = $cart->items();
            foreach ($items as $itemHash=>$cartEntry) {
                if ($cartEntry instanceof CartEntry) {
                    $message .= $this->renderSellableItem($cartEntry->getItem());
                }
            }
        }
        else if ($item instanceof SellableItem && $form instanceof FastOrderProductForm) {
            $message .= $this->renderSellableItem($item);
        }
        else {
            throw new Exception(tr("Incorrect CTOR input parameters"));
        }

        $message .= "</table>";


        $message .= "\r\n\r\n";


        $this->body = $this->templateMessage($message);

        Debug::ErrorLog ("Message contents prepared ...");


    }

    protected function renderSellableItem(SellableItem $item)
    {
        $result = "";

        $result .= "<tr>";
        $result .= "<td>";
        $si = $item->getMainPhoto();
        $src = new URL($si->hrefImage(256,256))->fullURL();
        $result .= "<img src='$src'>";
        $result .= "</td>";

        $result .= "<td>";

        $result .= "Продукт: ".$item->getTitle();
        $result .= "\r\n";
        $result .= "Цена: ".$item->getPriceInfo()->getSellPrice()." ".Spark::Get(StoreConfig::DEFAULT_CURRENCY);
        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $result .= "Цена: ".formatPrice($item->getPriceInfo()->getSellPrice() / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE), Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY));
        }
        $result .= "\r\n";

        if ($item->variantsCount()>0) {
            $options = $item->getVariantNames();
            foreach ($options as $idx=>$option_name) {
                $vitem = $item->getVariant($option_name);
                $result .= $vitem->getName().": ".$vitem->getSelected();
                $result .= "\r\n";
            }
        }


        $productURL = new ProductURL();
        $productURL->setProductID($item->getProductID());
        $result .= "<a href='{$productURL->fullURL()}'>Виж продукта</a>";
        $result .= "\r\n";
        $result .= "</td>";
        $result .= "</tr>";

        return $result;
    }
}

?>
