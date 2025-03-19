<?php
include_once("mailers/Mailer.php");
include_once("store/beans/OrdersBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("beans/UsersBean.php");
include_once("store/utils/cart/Cart.php");

class OrderConfirmationMailer extends Mailer
{

    public function __construct(int $orderID)
    {

        parent::__construct();

        debug ("Accessing OrderBean with orderID: $orderID");
        $orders = new OrdersBean();
        $order = $orders->getByID($orderID);

        $userID = (int)$order["userID"];


        debug ("Accessing UsersBean with order userID: $userID");

        $users = new UsersBean();
        $user = $users->getByID($userID, "userID", "fullname", "email", "phone");

        debug ("Preparing message ...");

        $this->to = $user["email"];
        $this->subject = "Потвърждение на поръчка от ".SITE_DOMAIN;

        $message = "Здравейте, {$user["fullname"]}\r\n\r\n";
        $message .= "Изпращаме Ви това съобщение за да Ви уведомим, че поръчка Ви е приета за обработка. ";
        $message .= "\r\n\r\n";

        $order_link = SITE_URL . LOCAL . "/account/order_details.php?orderID=$orderID";

        $message .= "Можете да видите поръчката си в меню ";
        $message .= "<a href='$order_link'>моят профил -> поръчки</a>";

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

        $message .= "Адрес за доставка: " . "\r\n";
        if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
            $message .= $order["delivery_address"];
        }
        else if ($option->getID() == DeliveryOption::USER_ADDRESS) {
            $address_form = new InputForm();
            $address_form->unserializeXML($order["delivery_address"]);
            ob_start();
            $address_form->renderPlain();
            $message .= ob_get_contents();
            ob_end_clean();
        }

        $message .= "\r\n";

        $message .= "Поръчани продукти:\r\n\r\n";

        $message .= "<table border=1>";
        $message .= "<tr>";
        $message .= "<th>#</th><th>Продукт</th><th>Брой</th><th>Ед.Цена</th><th>Сума</th>";
        $message .= "</tr>";

        debug ("Preparing order items table ...");

        $order_items = new OrderItemsBean();
        $qry = $order_items->query("product", "position", "qty", "price");
        $qry->select->where()->add("orderID", $orderID);
        $qry->select->order_by = " position ASC ";
        $qry->exec();

        while ($item = $qry->next()) {

            $message .= "<tr>";

            $details = explode("//", $item["product"]);

            $message .= "<td>{$item["position"]}</td>";

            $message .= "<td>";
            foreach ($details as $index => $value) {

                $message .= $value . "<BR>";
            }
            $message .= "</td>";

            $message .= "<td>" . $item["qty"] . "</td>";
            $message .= "<td>" . sprintf("%0.2f лв.", $item["price"]) . "</td>";
            $message .= "<td>" . sprintf("%0.2f лв.", ((int)$item["qty"] * $item["price"])) . "</td>";

            $message .= "</tr>";
        }


        $message .= "</table>";

        $message .= "\r\n";
        $message .= "\r\n";

        $message .= "Продкти общо: " . sprintf("%0.2f лв.", ($order["total"] - (($order["delivery_price"]>0) ? $order["delivery_price"] : 0))) . "\r\n";
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
        $message .= "Цена доставка: $delivery_text\r\n";
        $message .= "Поръчка общо: " . sprintf("%0.2f лв.", $order["total"]) . "\r\n";


        $message .= "\r\n";
        $message .= "\r\n";

        $message .= "Поздрави,\r\n";
        $message .= SITE_DOMAIN;

        $this->body = $this->templateMessage($message);

        debug ("Message contents prepared ...");


    }

}

?>
