<?php
include_once("store/beans/OrdersBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("beans/UsersBean.php");
include_once("store/utils/cart/Cart.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/utils/url/ProductURL.php");
include_once("input/renderers/CheckField.php");
include_once("components/renderers/cells/DateCell.php");

/**
 * The values of an order for its mails (store/mails/order_summary.php and the order mails): the order, the client,
 * the delivery, the items and the totals. Texts are as stored (the templates escape them); the '_html' values are
 * HTML already (prices with the currency, the address of the order).
 */
class OrderMailData
{
    /**
     * @return array<string, mixed>
     * @throws Exception the order does not exist
     */
    public static function Load(int $orderID) : array
    {
        $orders = new OrdersBean();
        $order = $orders->getByID($orderID);
        if (!is_array($order)) throw new Exception("Order not found: $orderID");

        $users = new UsersBean();
        $client = $users->getByID((int)$order["userID"], "fullname", "email", "phone");

        $delivery = new Delivery();
        $delivery->setSelectedCourier($order["delivery_courier"]);
        $courier = $delivery->getSelectedCourier();
        $courier->setSelectedOption($order["delivery_option"]);
        $option = $courier->getSelectedOption();

        $accountURL = new URL(Spark::Get(Config::LOCAL) . "/account/orders/details");
        $accountURL->add(new URLParameter("orderID", (string)$orderID));
        $adminURL = new URL(Spark::Get(Config::LOCAL) . "/admin/orders/details");
        $adminURL->add(new URLParameter("orderID", (string)$orderID));

        $deliveryPrice = (float)$order["delivery_price"];
        $discount = (float)($order["discount_amount"] ?? 0);
        if ($deliveryPrice > 0) $deliveryHTML = formatPrice($deliveryPrice);
        else if ($deliveryPrice == 0) $deliveryHTML = Spark::AttributeValue(tr("Free"));
        else $deliveryHTML = Spark::AttributeValue(tr("By the tariff of the courier"));

        return array(
            "orderID" => $orderID,
            "date" => OrderMailData::DateText((string)$order["order_date"]),
            "status" => (string)$order["status"],
            "client" => is_array($client) ? $client : array("fullname" => "", "email" => "", "phone" => ""),
            "courier" => $courier->getTitle(),
            "option" => $option->getTitle(),
            "address_html" => OrderMailData::AddressHTML($order, $option),
            "invoice" => ((int)$order["require_invoice"] > 0),
            "note" => trim((string)$order["note"]),
            "items" => OrderMailData::Items($orderID),
            "products_total_html" => formatPrice($order["total"] - max($deliveryPrice, 0) + $discount),
            "discount_html" => ($discount > 0) ? formatPrice($discount) : "",
            "delivery_html" => $deliveryHTML,
            "total_html" => formatPrice($order["total"]),
            "account_url" => (string)$accountURL->fullURL(),
            "admin_url" => (string)$adminURL->fullURL(),
        );
    }

    /**
     * '2026-11-25 10:15:00' as '25 ноември 2026, 10:15' - the month in the language of the site
     */
    public static function DateText(string $date) : string
    {
        $time = strtotime($date);
        if ($time === false) return $date;
        return date("j ", $time) . DateCell::MonthName($time) . date(" Y, H:i", $time);
    }

    /**
     * The delivery address as HTML: the address form of the client (door) or the courier office text
     */
    protected static function AddressHTML(array $order, DeliveryOption $option) : string
    {
        if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
            return nl2br(Spark::AttributeValue((string)$order["delivery_address"]));
        }
        if ($option->getID() != DeliveryOption::USER_ADDRESS) return "";
        try {
            $form = new ClientAddressInputForm();
            $form->unserializeXML((string)$order["delivery_address"]);
            $lines = array();
            foreach ($form->inputNames() as $name) {
                $input = $form->getInput($name);
                $value = trim((string)$input->getValue());
                if ($value === "" || $input->getRenderer() instanceof CheckField) continue;
                $lines[] = Spark::AttributeValue($value);
            }
            return implode("<br>", $lines);
        }
        catch (Exception $e) {
            Debug::ErrorLog("Order {$order["orderID"]} address: " . $e->getMessage());
            return "";
        }
    }

    /**
     * The items: position, lines (the product name and its options - "name//option: value"), qty, the prices
     * @return array<int, array<string, mixed>>
     */
    protected static function Items(int $orderID) : array
    {
        $items = array();
        $bean = new OrderItemsBean();
        $query = $bean->queryField("orderID", (string)$orderID);
        $query->stmt->columns("prodID", "price", "qty", "product");
        $query->stmt->order("position", OrderDirection::ASC);
        $query->exec();
        $position = 0;
        while ($item = $query->next()) {
            $position++;
            $url = new ProductURL();
            $url->setProductID((int)$item["prodID"]);
            $items[] = array(
                "position" => $position,
                "lines" => explode("//", (string)$item["product"]),
                "qty" => (int)$item["qty"],
                "price_html" => formatPrice($item["price"]),
                "amount_html" => formatPrice($item["qty"] * $item["price"]),
                "url" => (string)$url->fullURL(),
            );
        }
        $query->free();
        return $items;
    }
}
