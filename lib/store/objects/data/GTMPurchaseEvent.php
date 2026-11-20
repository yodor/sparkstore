<?php
include_once("objects/data/GTMCommand.php");
include_once("store/beans/OrdersBean.php");
include_once("store/beans/OrderItemsBean.php");

/**
 * GA4 'purchase' of an order (the checkout complete page): transaction_id, value, shipping, currency, items.
 * Sent with the visitor's consent like every gtag command (SparkGTM).
 */
class GTMPurchaseEvent extends GTMCommand
{
    public function __construct(int $orderID)
    {
        parent::__construct(GTMCommand::COMMAND_EVENT);
        $this->setType("purchase");

        $orders = new OrdersBean();
        $order = $orders->getByID($orderID, "total", "delivery_price", "discount_amount");

        $this->addParameter("transaction_id", (string)$orderID);
        $this->addParameter("affiliation", (string)Spark::Get(Config::SITE_DOMAIN));
        $this->addParameter("value", (string)round((float)$order["total"], 2));
        $this->addParameter("shipping", (string)round((float)$order["delivery_price"], 2));
        $this->addParameter("currency", (string)Spark::Get(StoreConfig::DEFAULT_CURRENCY));

        $items = array();
        $orderItems = new OrderItemsBean();
        $query = $orderItems->query("prodID", "product", "price", "qty");
        $query->stmt->where()->match("orderID", $orderID);
        $query->exec();
        $index = 0;
        while ($row = $query->next()) {
            $items[] = array(
                "item_id" => (string)$row["prodID"],
                "item_name" => (string)$row["product"],
                "index" => $index++,
                "price" => round((float)$row["price"], 2),
                "quantity" => (int)$row["qty"],
            );
        }
        $this->addParameter("items", $items);
    }
}
