<?php
include_once("utils/ImageSizes.php");
include_once("mailers/Mailer.php");
include_once("store/features/fast_order/forms/FastOrderProductForm.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/cart/Cart.php");
include_once("store/utils/url/ProductURL.php");


class FastOrderAdminMailer extends Mailer
{

    /**
     * Used to mail the fast order to admin - the mail template store/features/fast_order/mails/fast_order_admin.php
     * If SellableItem $item is not null and $form is FastOrderProductForm mail single item
     * If $form is ClientAddressInputForm mail all items in the cart
     * @param InputForm $form
     * @param SellableItem|null $item
     * @throws Exception
     */
    public function __construct(InputForm $form, ?SellableItem $item=null)
    {

        parent::__construct();

        $this->to = Spark::Get(StoreConfig::ORDER_EMAIL);
        $this->subject = tr("Fast order at %s", Spark::Get(Config::SITE_DOMAIN));

        $items = array();
        //fast order cart items
        if ($form instanceof ClientAddressInputForm) {
            $cart = Cart::Instance();
            if ($cart->itemsCount()<1) throw new Exception(tr("Your cart is empty"));
            foreach ($cart->items() as $cartEntry) {
                if ($cartEntry instanceof CartEntry) {
                    $items[] = $this->itemData($cartEntry->getItem(), $cartEntry->getQuantity());
                }
            }
        }
        else if ($item instanceof SellableItem && $form instanceof FastOrderProductForm) {
            $items[] = $this->itemData($item, 1);
        }
        else {
            throw new Exception("Incorrect CTOR input parameters");
        }

        //entered by the visitor - the template escapes it
        $this->useTemplate("fast_order_admin", array(
            "fullname" => (string)$form->getInput("fullname")->getValue(),
            "phone" => (string)$form->getInput("phone")->getValue(),
            "address" => $form->haveInput("address") ? (string)$form->getInput("address")->getValue() : "",
            "items" => $items,
        ));
    }

    /**
     * The values of an ordered item for the template: title, url, photo, price_html, qty, options (name => value)
     */
    protected function itemData(SellableItem $item, int $qty) : array
    {
        $photo = "";
        //a product without photos has no main photo
        $si = $item->getMainPhoto();
        if ($si) {
            $photo = (string)(new URL($si->hrefImage(ImageSize::Thumbnail->value, ImageSize::Thumbnail->value)))->fullURL();
        }

        $options = array();
        if ($item->variantsCount()>0) {
            foreach ($item->getVariantNames() as $option_name) {
                $vitem = $item->getVariant($option_name);
                $options[(string)$vitem->getName()] = (string)$vitem->getSelected();
            }
        }

        $productURL = new ProductURL();
        $productURL->setProductID($item->getProductID());
        $productURL->setProductName($item->getTitle());

        return array(
            "title" => $item->getTitle(),
            "url" => (string)$productURL->fullURL(),
            "photo" => $photo,
            "price_html" => formatPrice($item->getPriceInfo()->getSellPrice()),
            "qty" => $qty,
            "options" => $options,
        );
    }
}
