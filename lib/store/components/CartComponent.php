<?php
include_once("components/Component.php");
include_once("store/utils/Cart.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductColorPhotosBean.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductInventoryBean.php");

class CartComponent extends Component implements IHeadContents
{

    protected $heading_text = "";
    protected $modify_enabled = TRUE;

    protected $total = 0.0;
    protected $order_total = 0.0;
    protected $delivery_price = 0.0;

    /**
     * Products
     * @var ProductsBean
     */
    protected $products;

    /**
     * Product inventories
     * @var ProductInventoryBean
     */
    protected $inventory;

    /**
     * color series
     * @var ProductColorPhotosBean
     */
    protected $color_photos;

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

    /**
     * @var DBDriver
     */
    protected $db;

    /**
     * @var ProductPricesSQL
     */
    protected $price_select;

    public function __construct()
    {
        parent::__construct();

        $this->inventory = new ProductInventoryBean();
        $this->color_photos = new ProductColorPhotosBean();
        $this->products = new ProductsBean();
        $this->product_photos = new ProductPhotosBean();
        $this->image_popup = new ImagePopup();
        $this->image_popup->setPhotoSize(-1, 100);
        $this->image_popup->getStorageItem()->enableExternalURL(TRUE);

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

    protected function renderCartItem(int $position, CartItem $cartItem)
    {


        $piID = $cartItem->getID();

        $item = $this->inventory->getByID($cartItem->getID());

        $prodID = $item["prodID"];

        $product = $this->products->getByID($prodID);

        //product inventory ID
        echo "<td field='position'>";
        if ($this->modify_enabled) {
            echo "<a class='item_remove' href='cart.php?remove&piID=$piID'>&#8855;</a>";
        }
        else {
            echo ($position+1);
        }
        echo "</td>";

        //only one photo here
        echo "<td field='product_photo'>";

        $product_url = LOCAL . "/products/details.php?prodID=$prodID&piID=$piID";
        $this->image_popup->setAttribute("href",  fullURL($product_url));

        $pclrID = (int)$item["pclrID"];

        $pclrpID = -1;

        $photo_found = FALSE;
        if ($pclrID > 0) {
            $pclrpID = $this->color_photos->getFirstPhotoID($pclrID);

            if ($pclrpID > 0) {
                $this->image_popup->setID($pclrpID);
                $this->image_popup->setBeanClass(get_class($this->color_photos));
                $photo_found = TRUE;
            }
        }
        if (!$photo_found) {
            $ppID = $this->product_photos->getFirstPhotoID($prodID);
            if ($ppID > 0) {
                $this->image_popup->setID($ppID);
                $this->image_popup->setBeanClass(get_class($this->product_photos));
            }
        }

        $this->image_popup->render();

        echo "</td>";

        echo "<td field='product_model'>";
        //         trbean($prodID, "product_name", $product, $this->products);

        echo $product["product_name"] . "<BR>";
        if ($item["color"]) {
            echo tr("Цвят") . ": " . $item["color"] . "<BR>";
        }
        if ($item["size_value"]) {
            echo tr("Размер") . ": " . $item["size_value"] . "<BR>";
        }

        echo tr("Код") . ": " . $piID . "-" . $prodID;

        echo "</td>";

        echo "<td field='qty'>";
        echo "<label>" . tr("Количество") . ": </label>";
        //             echo "<div class='qty'>";
        if ($this->modify_enabled) {
            echo "<a class='qty_adjust minus' href='cart.php?decrement&piID=$piID'>&#8854;</a>";
        }
        echo "<span class='cart_qty'>" . $cartItem->getQuantity() . "</span>";
        if ($this->modify_enabled) {
            echo "<a class='qty_adjust plus' href='cart.php?increment&piID=$piID'>&#8853;</a>";
        }
        //             echo "</div>";
        echo "</td>";


        echo "<td field='price'>";
        echo "<label>" . tr("Цена") . ": </label>";
        //                 $price = $currency_rates->getPrice($item["sell_price"]);
        //                 echo sprintf("%0.2f ".$price["symbol"] , $price["price_value"]);


        echo "<span>" . formatPrice($cartItem->getPrice()) . "</span>";

        echo "</td>";

        echo "<td field='line-total'>";
        echo "<label>" . tr("Общо") . ": </label>";
        //                 $line_total = ($qty * (float)$price["price_value"]);
        //                 echo sprintf("%0.2f ".$price["symbol"], $line_total );

        echo "<span>" . formatPrice($cartItem->getLineTotal()) . "</span>";

        echo "</td>";

        //         echo "<td field='actions'>";

        //         echo "</td>";

    }

    public function startRender()
    {
        parent::startRender();

        if ($this->heading_text) {
            echo "<div class='heading'>";
            echo $this->heading_text;
            echo "</div>";
        }

        $this->table->startRender();
    }

    public function finishRender()
    {
        $this->table->finishRender();
        parent::finishRender();
    }

    protected function renderImpl()
    {

        $items = Cart::Instance()->items();

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

        if (count($items) == 0) {
            echo "<tr>";
            echo "<td colspan=6 field='cart_empty'>";
            echo tr("Вашата кошница е празна");
            echo "</td>";
            echo "</tr>";
        }
        else {

            $items_listed = 0;
            $num_items_total = 0;

            foreach ($items as $piID => $cartItem) {

                if (!$cartItem instanceof CartItem) continue;

                $item = "";
                ob_start();
                try {

                    echo "<tr label='item'>";

                    $this->renderCartItem($items_listed, $cartItem);

                    $total += $cartItem->getLineTotal();

                    $num_items_total += ($cartItem->getQuantity());

                    echo "</tr>";

                    $items_listed++;
                    $item = ob_get_contents();


                }
                catch (Exception $e) {
                    Cart::Instance()->remove($piID);
                    Cart::Instance()->store();
                }
                ob_end_clean();
                echo $item;
            }

        }

        $order_total = $total;

        if ($total > 0) {
            $this->total = $total;

            echo "<tr label='summary-total'>";
            //             echo "<td colspan=4 rowspan=3 field='items_total'>";
            //             echo $num_items_total." ";
            //             echo ($num_items_total>1)? tr("Продукта") : tr("Продукт");
            //             echo "</td>";

            echo "<td colspan=5 class='label amount_total'>";
            echo tr("Продукти общо") . ": ";
            echo "</td>";

            echo "<td class='value amount_total'>";
            echo formatPrice($total);
            echo "</td>";

            echo "</tr>";

            $selected_courier = Cart::Instance()->getDelivery()->getSelectedCourier();
            $selected_option = NULL;
            if ($selected_courier) {
                $selected_option = $selected_courier->getSelectedOption();
            }

            if ($selected_option != NULL) {

                echo "<tr label='summary-delivery'>";

                echo "<td colspan=5 class='label delivery' >";
                echo tr("Доставка") . ": ";
                echo "</td>";

                echo "<td class='value delivery'>";


                $delivery_price = $selected_option->getPrice();

                $this->delivery_price = $delivery_price;

                //              $price = $currency_rates->getPrice($delivery_price);
                $order_total = $order_total + $delivery_price;
                if ($delivery_price>0) {
                    echo formatPrice($delivery_price);
                }
                else {
                    echo tr("Безплатна");
                }
                echo "</td>";

                echo "</tr>";

                echo "<tr label='summary-order-total'>";

                echo "<td colspan=5 class='label order_total'>";
                echo tr("Поръчка общо") . ": ";
                echo "</td>";

                echo "<td class='value order_total'>";
                echo formatPrice($order_total);
                echo "</td>";

                echo "</tr>";

            }

        }


        $this->total = $total;
        $this->order_total = $order_total;

    }

}
