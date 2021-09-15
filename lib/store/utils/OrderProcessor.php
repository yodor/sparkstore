<?php
// $GLOBALS["DEBUG_OUTPUT"] = 1;

include_once("store/utils/Cart.php");
include_once("store/beans/OrdersBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("store/beans/CourierAddressesBean.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/forms/DeliveryAddressForm.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductInventoryBean.php");
include_once("store/beans/ProductColorPhotosBean.php");
include_once("beans/ConfigBean.php");

class OrderProcessor
{

    protected $orderID = -1;

    /**
     * @var bool flag to enable lowering the stock amount of the purchased inventory
     */
    protected $manage_stock_amount = false;

    /**
     * @var bool flag to enable increasing the order counter of the purchased inventory
     */
    protected $manage_order_counter = true;

    public function __construct()
    {

    }

    public function setManageStockAmount(bool $mode)
    {
        $this->manage_stock_amount = $mode;
    }

    public function setManageOrderCount(bool $mode)
    {
        $this->manage_order_counter = $mode;
    }

    public function getOrderID() : int
    {
        return $this->orderID;
    }

    public function createOrder()
    {


        $cart = Cart::Instance();

        if ($cart->itemsCount() < 1) throw new Exception("Вашата кошница е празна");
        if (is_null($cart->getDelivery()->getSelectedCourier())) throw new Exception("Не сте избрали куриер");
        if (is_null($cart->getDelivery()->getSelectedCourier()->getSelectedOption())) throw new Exception("Не сте избрали адрес за доставка");

        $page = StorePage::Instance();
        if ($page->getUserID() < 1) {
            debug("Login required ... ");
            throw new Exception("Изисква регистриран потребител");
        }

        $userID = $page->getUserID();

        debug("Using userID='$userID'");

        $db = DBConnections::Get();

        $this->orderID = -1;

        try {

            $db->transaction();

            $inventory = new ProductInventoryBean();

            $orders = new OrdersBean();
            $eab = new CourierAddressesBean();

            $items = $cart->items();


            $order = array();

            $courier = $cart->getDelivery()->getSelectedCourier();
            $option = $courier->getSelectedOption();

            $order["delivery_price"] = $option->getPrice();

            $order["delivery_courier"] = $courier->getID();

            $order["delivery_option"] = $option->getID();

            $uab = new ClientAddressesBean();

            if ($option->getID() == DeliveryOption::USER_ADDRESS) {

                $uabrow = $uab->getResult("userID", $userID);

                $form = new ClientAddressInputForm();
                $form->loadBeanData($uabrow[$uab->key()], $uab);

                $order["delivery_address"] = $db->escape($form->serializeXML());

            }
            else if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
                $qry = $eab->queryField("userID", $userID, 1, "office");
                $num = $qry->exec();
                if ($num < 1) throw new Exception("Недостъпен адрес за доставка");
                $ekont_address = $qry->next();
                $order["delivery_address"] = $db->escape($ekont_address["office"]);
            }
            else {
                throw new Exception("Недостъпен начин на доставка");
            }

            $order["note"] = $cart->getNote();
            $order["require_invoice"] = (int)$cart->getRequireInvoice();
            $order["userID"] = $userID;

            $order_total = (float)0;

            foreach ($items as $piID => $cartItem) {
                if (!$cartItem instanceof CartItem) continue;
                $order_total = $order_total + $cartItem->getLineTotal();
            }

            $order_total = $order_total + $option->getPrice();
            $order["total"] = $order_total;

            $this->orderID = $orders->insert($order, $db);
            if ($this->orderID < 1) throw new Exception("Unable to insert order: " . $db->getError());

            debug("Created orderID: {$this->orderID} - for clientID: $userID - Filling order items ...");

            $order_items = new OrderItemsBean();
            $products = new ProductsBean();

            $photos = new ProductColorPhotosBean();
            $gallery_photos = new ProductPhotosBean();

            $pos = 1;
            foreach ($items as $piID => $cartItem) {

                if (!$cartItem instanceof CartItem) continue;

                $item = $inventory->getByID($piID);
                $prodID = (int)$item["prodID"];

                $product = $products->getByID($prodID, "prodID", "brand_name", "product_name");

                $product_details = "Продукт||{$product["product_name"]}//Цвят||{$item["color"]}//Размер||{$item["size_value"]}//Марка||{$product["brand_name"]}//Код|| {$piID}-{$prodID}";

                //try inventory image raw data else product photos
                $item_photo = NULL;

                $pclrID = (int)$item["pclrID"];
                $pclrpID = -1;
                if ($pclrID > 0) {
                    $pclrpID = $photos->getFirstPhotoID($pclrID);
                }

                try {
                    debug("Doing copy of product photos to order ");

                    //try product gallery photos
                    if ($pclrpID < 1) {
                        $ppID = $gallery_photos->getFirstPhotoID($prodID);
                        //no photo here too
                        if ($ppID < 1) {
                            debug("No product source photo to store into order items. ProdID=$prodID | InvID=$piID ");
                        }
                        else {
                            //copy
                            $photo_row = $gallery_photos->getByID($ppID);
                            $item_photo = $photo_row["photo"];
                        }

                    }
                    else {
                        $photo_row = $photos->getByID($pclrpID);
                        $item_photo = $photo_row["photo"];
                    }
                }
                catch (Exception $e) {
                    debug("Unable to copy source product photos. ProdID=$prodID | InvID=$piID | Exception: " . $e->getMessage());
                }



                $order_item = array();
                $order_item["piID"] = $piID;
                $order_item["qty"] = $cartItem->getQuantity();
                $order_item["price"] = $cartItem->getPrice();
                $order_item["position"] = $pos;
                $order_item["orderID"] = $this->orderID;
                $order_item["product"] = $product_details;
                $order_item["prodID"] = $prodID;
                $order_item["photo"] = DBConnections::Get()->escape($item_photo);

                $itemID = $order_items->insert($order_item, $db);
                if ($itemID < 1) throw new Exception("Unable to insert order item: " . $db->getError());

                $inventory_update = array();

                if ($this->manage_stock_amount) {
                    $inventory_update["stock_amount"] = ($item["stock_amount"] - $cartItem->getQuantity());
                }
                if ($this->manage_order_counter) {
                    $inventory_update["order_counter"] = ($item["order_counter"] + 1);
                }

                if (count($inventory_update)>0) {
                    if (!$inventory->update($piID, $inventory_update, $db)) throw new Exception("Unable to update inventory statistics: " . $db->getError());
                }

                $pos++;
            }

            debug("OrderProcessor::createOrder() finalizing transaction for orderID='{$this->orderID}' ... ");
            $db->commit();

            $cart->clear();
            $cart->store();

            debug("OrderProcessor::createOrder() completed for orderID='{$this->orderID}' ... ");
        }
        catch (Exception $e) {
            $this->orderID = -1;
            $db->rollback();

            throw new Exception($e->getMessage());

        }
        return $this->orderID;
    }

}

?>
