<?php
// $GLOBALS["DEBUG_OUTPUT"] = 1;

include_once("store/utils/cart/Cart.php");
include_once("store/beans/OrdersBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("store/beans/CourierAddressesBean.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/forms/DeliveryAddressForm.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/beans/ProductsBean.php");

include_once("beans/ConfigBean.php");

class OrderProcessor
{

    protected $orderID = -1;

    /**
     * @var bool flag to enable lowering the stock amount of the purchased product
     */
    protected $manage_stock_amount = false;

    /**
     * @var bool flag to enable increasing the order counter of the purchased product
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

            foreach ($items as $itemHash => $cartEntry) {
                if (!$cartEntry instanceof CartEntry) continue;
                $order_total = $order_total + $cartEntry->getLineTotal();
            }

            $discount_amount = $cart->getDiscount()->amount();
            $order["discount_amount"] = $discount_amount;
            $order_total = $order_total - $discount_amount;

            $order_total = $order_total + $option->getPrice();
            $order["total"] = $order_total;

            $this->orderID = $orders->insert($order, $db);
            if ($this->orderID < 1) throw new Exception("Unable to insert order: " . $db->getError());

            debug("Created orderID: {$this->orderID} - for clientID: $userID - Filling order items ...");

            $order_items = new OrderItemsBean();

            $amounts = array();

            $pos = 1;
            foreach ($items as $itemHash => $cartEntry) {

                if (!$cartEntry instanceof CartEntry) continue;


                $prodID = $cartEntry->getItem()->getProductID();

                $item = $cartEntry->getItem();
                $description =  tr("Product").": ".$item->getTitle() . "//";
                $description.=  tr("Code").": ".$item->getProductID() . "//";

                $variants = $item->getVariantNames();
                foreach ($variants as $idx=>$variantName) {
                    $variantItem = $item->getVariant($variantName);
                    if ($variantItem instanceof VariantItem) {
                        $description.=tr($variantName).": ".$variantItem->getSelected()."//";
                    }
                }

                $item_photo = "";
                try {
                    debug("Doing copy of product photos to order ");


                    $sitem = $cartEntry->getItem()->getMainPhoto();
                    if ($sitem instanceof StorageItem) {
                        $photo_class = new $sitem->className();
                        if ($photo_class instanceof DBTableBean) {
                            $result = $photo_class->getByID($sitem->id, "photo");
                            $item_photo = (string)$result["photo"];
                        }
                    }

                }
                catch (Exception $e) {
                    debug("Unable to copy source product photos. ProdID=$prodID | Exception: " . $e->getMessage());
                }

                $order_item = array();

                $order_item["qty"] = $cartEntry->getQuantity();
                $order_item["price"] = $cartEntry->getPrice();
                $order_item["position"] = $pos;
                $order_item["orderID"] = $this->orderID;
                $order_item["product"] = $description;
                $order_item["prodID"] = $prodID;
                $order_item["photo"] = DBConnections::Get()->escape($item_photo);

                $itemID = $order_items->insert($order_item, $db);
                if ($itemID < 1) throw new Exception("Unable to insert order item: " . $db->getError());

                if (!isset($amounts[$prodID])) {
                    $amounts[$prodID] = $cartEntry->getQuantity();
                }
                $amounts[$prodID] = (int)$amounts[$prodID] + $cartEntry->getQuantity();

                $pos++;
            }

            debug("OrderProcessor::createOrder() finalizing transaction for orderID='{$this->orderID}' ... ");
            $db->commit();

            $cart->clear();
            $cart->store();

            if ($this->manage_order_counter || $this->manage_stock_amount) {
                foreach ($amounts as $prodID => $amount) {
                    @$this->updateCounterStock($prodID, $amount);
                }
            }


            debug("OrderProcessor::createOrder() completed for orderID='{$this->orderID}' ... ");
        }
        catch (Exception $e) {
            $this->orderID = -1;
            $db->rollback();

            throw new Exception($e->getMessage());

        }
        return $this->orderID;
    }

    protected function updateCounterStock(int $prodID, int $amount=1)
    {
        $sql = new SQLUpdate();
        $sql->from = "products p";
        if ($this->manage_order_counter) {
            $sql->set("p.order_counter", "p.order_counter+$amount");
        }
        if ($this->manage_stock_amount) {
            $sql->set("p.stock_amount", "p.stock_amount-$amount");
        }
        $sql->where()->add("p.prodID", $prodID);

        $db = DBConnections::Get();
        try {
            $db->transaction();
            $db->query($sql->getSQL());
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollback();
            debug("Unable to increment order_counter or stock_amount count: ".$db->getError());
        }
    }

}

?>