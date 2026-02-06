<?php
include_once("mailers/Mailer.php");
include_once("store/beans/OrdersBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("beans/UsersBean.php");
include_once("store/utils/cart/Cart.php");

class OrderConfirmationAdminMailer extends Mailer
{

    public function __construct(int $orderID)
    {

        parent::__construct();

        Debug::ErrorLog ("Accessing OrderBean with orderID: $orderID");
        $orders = new OrdersBean();
        $order = $orders->getByID($orderID);

        $userID = (int)$order["userID"];

        Debug::ErrorLog ("Accessing UsersBean with order userID: $userID");

        $users = new UsersBean();
        $user = $users->getByID($userID, "userID", "fullname", "email", "phone");

        Debug::ErrorLog ("Preparing message ...");

        $this->to = Spark::Get(StoreConfig::ORDER_EMAIL);
        $this->subject = "Нова поръчка на ".Spark::Get(Config::SITE_DOMAIN);

        $message = "Здравейте, \r\n\r\n";
        $message .= "Изпращаме Ви това съобщение за да Ви уведомим, че беше направена нова поръчка. ";
        $message .= "\r\n\r\n";

        $message .= "Поръчка Номер: $orderID \r\n";
        $message .= "Дата: {$order["order_date"]} \r\n";

        $delivery = new Delivery();
        $delivery->setSelectedCourier($order["delivery_courier"]);
        $courier = $delivery->getSelectedCourier();

        $courier->setSelectedOption($order["delivery_option"]);
        $option = $courier->getSelectedOption();

        $message .= "Куриер: " . $courier->getTitle() . "\r\n";
        $message .= "Начин на доставка: " . $option->getTitle() . "\r\n";

        $message .= "\r\n";

        $message .= "Поръчани продукти:\r\n\r\n";

        $message .= "<table border=1>";
        $message .= "<tr>";
        $message .= "<th>#</th><th>Продукт</th><th>Брой</th><th>Ед.Цена</th><th>Сума</th>";
        $message .= "</tr>";

        Debug::ErrorLog ("Preparing order items table ...");

        $order_items = new OrderItemsBean();
        $qry = $order_items->query("product", "position", "qty", "price");
        $qry->select->where()->add("orderID", $orderID);
        $qry->select->order_by = " position ASC ";
        $qry->exec();

        while ($item = $qry->next()) {

            $message .= "<tr>";

            $details = explode("//", $item["product"]);

            $message .= "<td>{$item["position"]}</td>";

//            $message .= "<td>";
//            foreach ($details as $index => $value) {
//                $data = explode("||", $value);
//                $message .= $data[0] . ": " . $data[1] . "<BR>";
//            }
//            $message .= "</td>";

            $message .= "<td>" . $item["qty"] . "</td>";
            $message .= "<td>" . formatPrice($item["price"], Spark::Get(StoreConfig::DEFAULT_CURRENCY)) . "</td>";
            $lineTotal = ((int)$item["qty"] * $item["price"]);
            $message .= "<td>" . formatPrice( $lineTotal , Spark::Get(StoreConfig::DEFAULT_CURRENCY) ) . "</td>";

            $message .= "</tr>";
        }


        $message .= "</table>";

        $message .= "\r\n";
        $message .= "\r\n";

        $itemsTotal = ($order["total"] - (($order["delivery_price"]>0) ? $order["delivery_price"] : 0));
        $message .= "Продкти общо: " . formatPrice($itemsTotal, Spark::Get(StoreConfig::DEFAULT_CURRENCY)) . "\r\n";
        $delivery_text = "";
        if ($order["delivery_price"]>0) {
            $delivery_text = formatPrice($order["delivery_price"], Spark::Get(StoreConfig::DEFAULT_CURRENCY));
        }
        else  if ($order["delivery_price"]==0) {
            $delivery_text = "Безплатна";
        }
        else {
            $delivery_text = "Според тарифния план на куриера";
        }
        $message .= "Цена доставка: $delivery_text\r\n";
        $message .= "Поръчка oбщо: " . formatPrice($order["total"], Spark::Get(StoreConfig::DEFAULT_CURRENCY)) . "\r\n";
        if (Spark::GetBoolean(StoreConfig::DOUBLE_PRICE_ENABLED)) {
            $message .= "Поръчка oбщо: " . formatPrice($order["total"] / Spark::GetFloat(StoreConfig::DOUBLE_PRICE_RATE), Spark::Get(StoreConfig::DOUBLE_PRICE_CURRENCY)) . "\r\n";
        }

        $message .= "\r\n";
        $message .= "\r\n";
        $message .= "Задължително финализирайте поръчката в администрацията на сайта от меню поръчки !!!";
        $message .= "\r\n";
        $message .= "\r\n";

        $message .= "Поздрави,\r\n";
        $message .= Spark::Get(Config::SITE_DOMAIN);

        $this->body = $this->templateMessage($message);

        Debug::ErrorLog ("Message contents prepared ...");


    }

}

?>
