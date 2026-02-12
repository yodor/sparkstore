<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");

include_once("beans/UsersBean.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/beans/CourierAddressesBean.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("store/beans/OrdersBean.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/utils/cart/Cart.php");

include_once("store/forms/InvoiceDetailsInputForm.php");
include_once("store/beans/InvoiceDetailsBean.php");


$page = new AdminPage();

$page->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL)."/css/print.css");

$ekont_addresses = new CourierAddressesBean();
$client_addresses = new ClientAddressesBean();
$invoices = new InvoiceDetailsBean();
$items = new OrderItemsBean();
$orders = new OrdersBean();

$clients = new UsersBean();

$sel = new SQLSelect();


$orderID = -1;

$order = NULL;

if (isset($_GET["orderID"])) {
    $orderID = (int)$_GET["orderID"];
}
$qry = $orders->queryFull();

$qry->select->where()->add("orderID", $orderID);
$qry->select->limit = " 1 ";
$num = $qry->exec();

$page->setName(tr("Order Details").": ".$orderID);

$order = $qry->next();

$page->startRender();

$page->setTitle(tr("Детайли за поръчка"));

echo "<a class='ColorButton' href='javascript:window.print();'>".tr("Print")."</a>";

echo "<div class='panel order_details'>";
    echo "<div class='group'>";

        echo "<div class='item order_num'>";
        echo "<label>" . tr("Номер на поръчка") . "</label>";
        echo "<span>" . $orderID . "</span>";
        echo "</div>";

        echo "<div class='item order_date'>";
        echo "<label>" . tr("Дата") . "</label>";
        echo "<span>" . $order["order_date"] . "</span>";
        echo "</div>";

        echo "<div class='item status'>";
        echo "<label>" . tr("Състояние") . "</label>";
        echo "<span>" . tr($order["status"]) . "</span>";
        echo "</div>";


    echo "</div>";//group

    echo "<div class='group'>";

        $delivery = new Delivery();
        $delivery->setSelectedCourier($order["delivery_courier"]);
        $courier = $delivery->getSelectedCourier();
        $courier->setSelectedOption($order["delivery_option"]);
        $option = $courier->getSelectedOption();

        echo "<div class='item delivery_courier'>";
//            echo "<label>" . tr("Куриер") . "</label>";
            echo "<span>" . $courier->getTitle() . "</span>";
        echo "</div>";

        echo "<div class='item delivery_option'>";
//            echo "<label>" . tr("Начин на доставка") . "</label>";
            echo "<span>" . $option->getTitle() . "</span>";
        echo "</div>";

        echo "<div class='item delivery_address'>";
//        echo "<label>" . tr("Адрес за доставка") . "</label>";

            if ($option->getID() == DeliveryOption::USER_ADDRESS) {
                try {
                    $caform = new ClientAddressInputForm();
                    $caform->unserializeXML($order["delivery_address"]);
                    $caform->renderPlain();
                }
                catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            else if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
                echo "<div class='ClientAddressList'>" . str_replace("\r\n", "<BR>", $order["delivery_address"]) . "</div>";
            }

        echo "</div>";

    echo "</div>";

    echo "<div class='group'>";

        echo "<div class='item require_invoice'>";
            echo "<label>" . tr("Фактуриране") . "</label>";
            echo "<span>" . (($order["require_invoice"] > 0) ? tr("Да") : tr("Не")) . "</span>";
        echo "</div>";

        if ($order["require_invoice"]>0) {
            echo "<div class='item invoice_details'>";

            $invbean = new InvoiceDetailsBean();
            $invform = new InvoiceDetailsInputForm();

            $result = $invbean->getByRef("userID", $order["userID"], $invbean->key());

            $invform->loadBeanData($result[$invbean->key()], $invbean);
            $invform->renderPlain();

            echo "</div>";
        }


    echo "</div>"; //group
echo "</div>";//panel

    echo "<div class='panel order_items'>";

        echo "<div class='group '>";

            echo "<div class='viewport'>";

                echo "<div class='line heading'>";
                    echo "<span>" . tr("Поз.") . "</span>";
                    echo "<span></span>";
                    echo "<span>" . tr("Продукт") . "</span>";
                    echo "<span>" . tr("Количество") . "</span>";
                    echo "<span>" . tr("Ед.цена") . "</span>";
                    echo "<span>" . tr("Сума") . "</span>";
                echo "</div>"; //line

                $qry = $items->queryField("orderID", $orderID);
                $qry->select->fields()->set("prodID", "itemID", "price", "qty", "product");
                $numItems = $qry->exec();

                $pos = 0;
                while ($item = $qry->next()) {
                    $pos++;
                    echo "<div class='line'>";
                    echo "<div class='item pos'>$pos</div>";


                    $prodID = $item["prodID"];

                    echo "<a class='item photo' href='" . Spark::Get(Config::LOCAL) . "/products/details.php?prodID=$prodID'>";

                    $href = StorageItem::Image($item["itemID"], get_class($items), 128, -1);
                    echo "<img src='$href'>";
                    echo "</a>";

                    echo "<div class='item product'>";

                    $details = explode("//", $item["product"]);
                    foreach ($details as $index => $value) {
                        //$data = explode("||", $value);
                        echo $value . "<BR>";
                    }
                    echo "</div>";
                    echo "<div class='item qty'>" . $item["qty"] . "</div>";
                    echo "<div class='item price'>" . sprintf("%0.2f лв.", $item["price"]) . "</div>";
                    echo "<div class='item amount'>" . sprintf("%0.2f лв.", ($item["qty"] * $item["price"])) . "</div>";
                    echo "</div>";
                }

            echo "</div>"; //viewport
        echo "</div>"; //group

        echo "<div class='group'>";
            echo "<div class='item products_total'>";
                echo "<label>" . tr("Продукти общо") . "</label>";
                echo "<span>" . formatPrice($order["total"] - (($order["delivery_price"]>0) ? $order["delivery_price"] : 0)) . "</span>";
            echo "</div>";

            $delivery_text = "";
            if ($order["delivery_price"]>0) {
                $delivery_text = sprintf("%0.2f лв.", $order["delivery_price"]);
            }
            else  if ($order["delivery_price"]==0) {
                $delivery_text = "Безплатна";
            }
            else {
                $delivery_text = "Според тарифния план на куриера";
            }

            echo "<div class='item delivey_price'>";
                echo "<label>" . tr("Доставка") . "</label>";
                echo "<span>$delivery_text</span>";
            echo "</div>";
            echo "<div class='item order_total'>";
                echo "<label>" . tr("Поръчка общо") . "</label>";
                echo "<span>" . formatPrice($order["total"]) . "</span>";
            echo "</div>";
        echo "</div>"; //group

        echo "<div class='group'>";
            echo "<div class='item order_note'>";
                echo "<label>" . tr("Бележка към поръчката") . "</label>";
                echo "<span>". $order["note"] ."</span>";
            echo "</div>";
        echo "</div>";

    echo "</div>";//panel



$page->finishRender();