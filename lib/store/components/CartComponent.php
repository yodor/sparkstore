<?php
include_once("components/Component.php");
include_once("store/utils/cart/Cart.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductsBean.php");
include_once("store/utils/SellableItem.php");

class CartComponent extends Component implements IHeadContents
{

    protected $heading_text = "";
    protected $modify_enabled = TRUE;

    protected $total = 0.0;
    protected $order_total = 0.0;
    protected $delivery_price = 0.0;
    protected $discount_amount = 0.0;

    /**
     * Products
     * @var ProductsBean
     */
    protected $products;


    //
    /**
     * product gallery photos (for non color serires)
     * @var ProductPhotosBean
     */
    protected $product_photos;

    /**
     * @var ImagePopup
     */
    protected $image_popup;

    /**
     * Main table holding the cart items
     * @var Component
     */
    protected $table;

    public function __construct()
    {
        parent::__construct();


        $this->products = new ProductsBean();
        $this->product_photos = new ProductPhotosBean();
        $this->image_popup = new ImagePopup();
        $this->image_popup->image()->setPhotoSize(-1, 100);
        $this->image_popup->image()->getStorageItem()->enableExternalURL(TRUE);

        $this->table = new Component();
        $this->table->setTagName("TABLE");
        $this->table->setClassName("cart_view");


    }

    public function getTable(): Component
    {
        return $this->table;
    }

    public function getImagePopup() : ImagePopup
    {
        return $this->image_popup;
    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/CartComponent.css";
        return $arr;
    }

//    public function setCart(Cart $cart)
//    {
//        $this->cart = $cart;
//    }

    public function setHeadingText(string $heading_text)
    {
        $this->heading_text = $heading_text;
    }

    public function setModifyEnabled(bool $mode)
    {
        $this->modify_enabled = $mode;
    }

    public function getTotal() : float
    {
        return $this->total;
    }

    public function getOrderTotal() : float
    {
        return $this->order_total;
    }

    public function getDeliveryPrice() : float
    {
        return $this->delivery_price;
    }

    protected function renderCartItem(int $position, CartEntry $cartEntry)
    {


        $item = $cartEntry->getItem();

        $itemHash = $item->hash();

        $prodID = $item->getProductID();

        //product inventory ID
        echo "<td field='position'>";
        if ($this->modify_enabled) {
            echo "<a class='item_remove' href='cart.php?remove&item=$itemHash'>&#8855;</a>";
        }
        else {
            echo ($position+1);
        }
        echo "</td>";

        //only one photo here
        echo "<td field='product_photo'>";

        $product_url = LOCAL . "/products/details.php?prodID=$prodID";
        $this->image_popup->setAttribute("href",  fullURL($product_url));

        $sitem = $item->getMainPhoto();

        $this->image_popup->image()->setStorageItem($sitem);

        $this->image_popup->render();

        echo "</td>";

        echo "<td field='product_model'>";
        //         trbean($prodID, "product_name", $product, $this->products);

        echo $item->getTitle() . "<BR>";

        $variants = $item->getVariantNames();
        foreach ($variants as $idx=>$variantName) {
            $variantItem = $item->getVariant($variantName);

            if ($variantItem instanceof VariantItem) {
                echo $variantName.": ".$variantItem->getSelected()."<BR>";
            }
        }


        //echo tr("Код") . ": " . $item->get . "-" . $prodID;

        echo "</td>";

        echo "<td field='qty'>";
        echo "<label>" . tr("Количество") . ": </label>";
        //             echo "<div class='qty'>";
        if ($this->modify_enabled) {
            echo "<a class='qty_adjust minus' href='cart.php?decrement&item=$itemHash'>&#8854;</a>";
        }
        echo "<span class='cart_qty'>" . $cartEntry->getQuantity() . "</span>";
        if ($this->modify_enabled) {
            echo "<a class='qty_adjust plus' href='cart.php?increment&item=$itemHash'>&#8853;</a>";
        }
        //             echo "</div>";
        echo "</td>";


        echo "<td field='price'>";
        echo "<label>" . tr("Цена") . ":&nbsp;</label>";
        //                 $price = $currency_rates->getPrice($item["sell_price"]);
        //                 echo sprintf("%0.2f ".$price["symbol"] , $price["price_value"]);


        if (DOUBLE_PRICE_ENABLED) {
            echo "<div class='addon_price'>" . formatPrice($cartEntry->getPrice() / DOUBLE_PRICE_RATE, "&euro;", true) . "</div>";
        }
        echo "<span>" . formatPrice($cartEntry->getPrice()) . "</span>";

        echo "</td>";

        echo "<td field='line-total'>";
        echo "<label>" . tr("Общо") . ":&nbsp;</label>";
        //                 $line_total = ($qty * (float)$price["price_value"]);
        //                 echo sprintf("%0.2f ".$price["symbol"], $line_total );

        if (DOUBLE_PRICE_ENABLED) {
            echo "<div class='addon_price'>" . formatPrice($cartEntry->getLineTotal() / DOUBLE_PRICE_RATE, "&euro;", true) . "</div>";
        }
        echo "<span>" . formatPrice($cartEntry->getLineTotal()) . "</span>";

        echo "</td>";

        //         echo "<td field='actions'>";

        //         echo "</td>";

    }

    public function startRender(): void
    {
        parent::startRender();

        if ($this->heading_text) {
            echo "<div class='heading'>";
            echo $this->heading_text;
            echo "</div>";
        }

        $this->table->startRender();
    }

    public function finishRender(): void
    {
        $this->table->finishRender();
        parent::finishRender();
    }

    protected function renderImpl(): void
    {

        $cart = Cart::Instance();

        echo "<tr label='heading'>";
        echo "
        <th>#</th>
        <th colspan=2 field='product'>" . tr("Продукт") . "</th>
        <th field='qty'>" . tr("Количество") . "</th>
        <th field='price'>" . tr("Ед. цена") . "</th>
        <th field='line_total'>" . tr("Общо") . "</th>
        ";
        echo "</tr>";

        //global $products, $photos, $currency_rates;

        $total = 0;

        if ($cart->itemsCount() == 0) {
            echo "<tr>";
            echo "<td colspan=6 field='cart_empty'>";
            echo tr("Вашата кошница е празна");
            echo "</td>";
            echo "</tr>";
        }
        else {

            $items_listed = 0;
            $num_items_total = 0;

            $itemsAll = $cart->items();

            foreach ($itemsAll as $itemHash => $cartEntry) {

                if (!($cartEntry instanceof CartEntry)) continue;

                echo "<tr label='item'>";

                $this->renderCartItem($items_listed, $cartEntry);

                $total += $cartEntry->getLineTotal();

                $num_items_total += ($cartEntry->getQuantity());

                echo "</tr>";

                $items_listed++;


            }



        }

        $order_total = $total;

        if ($total > 0) {
            $this->total = $total;

            echo "<tr class='summary items_total' label='items_total'>";
            //             echo "<td colspan=4 rowspan=3 field='items_total'>";
            //             echo $num_items_total." ";
            //             echo ($num_items_total>1)? tr("Продукта") : tr("Продукт");
            //             echo "</td>";

            echo "<td colspan=5 class='label'>";
            echo tr("Продукти общо") . ": ";
            echo "</td>";

            echo "<td class='value'>";

            if (DOUBLE_PRICE_ENABLED) {
                echo "<div class='addon_price'>" . formatPrice($total / DOUBLE_PRICE_RATE, "&euro;", true) . "</div>";
            }
            echo formatPrice($total);
            echo "</td>";

            echo "</tr>";

            $discount = $cart->getDiscount();
            $this->discount_amount = $discount->amount();

            $order_total = (float)$order_total - (float)$this->discount_amount;

            if ($discount->amount()) {
                echo "<tr class='summary discount' label='discount'>";

                echo "<td colspan=5 class='label'>";
                echo tr("Отстъпки") . ": ";
                echo "</td>";

                echo "<td class='value'>";
                echo $discount->label();
                echo "</td>";

                echo "</tr>";
            }

            $selected_courier = $cart->getDelivery()->getSelectedCourier();
            $selected_option = NULL;
            if ($selected_courier) {
                $selected_option = $selected_courier->getSelectedOption();
            }

            if ($selected_option != NULL) {

                echo "<tr class='summary delivery' label='delivery'>";

                echo "<td colspan=5 class='label' >";
                echo tr("Доставка") . ": ";
                echo "</td>";

                echo "<td class='value'>";


                $delivery_price = $selected_option->getPrice();

                //              $price = $currency_rates->getPrice($delivery_price);

                if ($delivery_price>0) {
                    if (DOUBLE_PRICE_ENABLED) {
                        echo "<div class='addon_price'>" . formatPrice($delivery_price / DOUBLE_PRICE_RATE, "&euro;", true) . "</div>";
                    }
                    echo formatPrice($delivery_price);
                }
                else {

                    if ($delivery_price==0) {
                        echo tr("Безплатна");
                    }
                    else {
                        echo tr("Според тарифния план на куриера");
                    }
                    $delivery_price = 0;
                }

                $this->delivery_price = $delivery_price;
                $order_total = $order_total + $this->delivery_price;
                echo "</td>";

                echo "</tr>";

            }

            echo "<tr class='summary order_total' label='order_total'>";

            echo "<td colspan=5 class='label'>";
            echo tr("Поръчка общо") . ": ";
            echo "</td>";

            echo "<td class='value'>";
            if (DOUBLE_PRICE_ENABLED) {
                echo "<div class='addon_price'>" . formatPrice($order_total / DOUBLE_PRICE_RATE, "&euro;", true) . "</div>";
            }
            echo formatPrice($order_total);
            echo "</td>";

            echo "</tr>";

        }


        $this->total = $total;
        $this->order_total = $order_total;

    }

}
