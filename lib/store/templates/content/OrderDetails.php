<?php
include_once("templates/TemplateContent.php");
include_once("components/ClosureComponent.php");
include_once("beans/UsersBean.php");
include_once("store/beans/OrdersBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/forms/InvoiceDetailsInputForm.php");
include_once("store/utils/cart/Cart.php");
include_once("store/responders/OrderConfirmationRequestResponder.php");

/**
 * Printable details of one order (?orderID=) - delivery, invoicing, items and totals.
 * Page action: send the order confirmation e-mail again.
 */
class OrderDetails extends TemplateContent
{
    protected int $orderID = -1;
    protected ?array $order = null;

    protected OrderConfirmationRequestResponder $confirmation;

    public function __construct()
    {
        parent::__construct();
        $this->confirmation = new OrderConfirmationRequestResponder();
    }

    public function setup(TemplateConfig $config): void
    {
        $this->orderID = (int)($_GET["orderID"] ?? -1);

        $orders = new OrdersBean();
        $query = $orders->queryFull();
        $query->stmt->where()->match("orderID", $this->orderID);
        $query->stmt->limit(1);
        $query->exec();
        $order = $query->next();
        $query->free();
        if (!$order) throw new Exception(tr("Order not found") . ": " . $this->orderID);
        $this->order = $order;

        $config->title = tr("Order Details") . ": " . $this->orderID;
        parent::setup($config);
    }

    public function fillPageActions(ActionCollection $collection): void
    {
        $print = new Action("print", "javascript:window.print();");
        $print->setContents(tr("Print"));
        $collection->append($print);

        $send = $this->confirmation->createAction(tr("Send confirmation e-mail"));
        $send->setURL(Module::PathURL("", $send->getURL()));
        $send->getURL()->add(new URLParameter("orderID", (string)$this->orderID));
        $collection->append($send);
    }

    public function initialize(): void
    {
        $order = $this->order;
        $orderID = $this->orderID;

        $this->cmp = new ClosureComponent(function () use ($order, $orderID) {
            //text entered by the client is escaped
            $e = fn($value) => htmlspecialchars((string)$value);

            echo "<div class='panel order_details'>";
            echo "<div class='group'>";
            echo "<div class='item order_num'><label>" . tr("Номер на поръчка") . "</label><span>" . $orderID . "</span></div>";
            echo "<div class='item order_date'><label>" . tr("Дата") . "</label><span>" . $e($order["order_date"]) . "</span></div>";
            echo "<div class='item status'><label>" . tr("Състояние") . "</label><span>" . tr($order["status"]) . "</span></div>";
            echo "</div>";

            echo "<div class='group'>";
            $delivery = new Delivery();
            $delivery->setSelectedCourier($order["delivery_courier"]);
            $courier = $delivery->getSelectedCourier();
            $courier->setSelectedOption($order["delivery_option"]);
            $option = $courier->getSelectedOption();

            echo "<div class='item delivery_courier'><span>" . $courier->getTitle() . "</span></div>";
            echo "<div class='item delivery_option'><span>" . $option->getTitle() . "</span></div>";
            echo "<div class='item delivery_address'>";
            if ($option->getID() == DeliveryOption::USER_ADDRESS) {
                try {
                    $form = new ClientAddressInputForm();
                    $form->unserializeXML($order["delivery_address"]);
                    $form->renderPlain();
                }
                catch (Exception $ex) {
                    echo $e($ex->getMessage());
                }
            }
            else if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
                echo "<div class='ClientAddressList'>" . nl2br($e($order["delivery_address"])) . "</div>";
            }
            echo "</div>";
            echo "</div>";

            echo "<div class='group'>";
            echo "<div class='item require_invoice'><label>" . tr("Фактуриране") . "</label><span>" . (($order["require_invoice"] > 0) ? tr("Да") : tr("Не")) . "</span></div>";
            if ($order["require_invoice"] > 0) {
                echo "<div class='item invoice_details'>";
                $invoices = new InvoiceDetailsBean();
                $result = $invoices->getByRef("userID", $order["userID"], $invoices->key());
                if ($result) {
                    $form = new InvoiceDetailsInputForm();
                    $form->loadBeanData($result[$invoices->key()], $invoices);
                    $form->renderPlain();
                }
                echo "</div>";
            }
            echo "</div>";
            echo "</div>";

            echo "<div class='panel order_items'>";
            echo "<div class='group'><div class='viewport'>";
            echo "<div class='line heading'><span>" . tr("Поз.") . "</span><span></span><span>" . tr("Продукт") . "</span><span>" . tr("Количество") . "</span><span>" . tr("Ед.цена") . "</span><span>" . tr("Сума") . "</span></div>";

            $items = new OrderItemsBean();
            $query = $items->queryField("orderID", (string)$orderID);
            $query->stmt->columns("prodID", "itemID", "price", "qty", "product");
            $query->exec();
            $rows = array();
            while ($item = $query->next()) $rows[] = $item;

            foreach ($rows as $pos => $item) {
                $prodID = (int)$item["prodID"];
                echo "<div class='line'>";
                echo "<div class='item pos'>" . ($pos + 1) . "</div>";
                echo "<a class='item photo' href='" . Spark::Get(Config::LOCAL) . "/products/details.php?prodID=$prodID'>";
                echo "<img src='" . StorageItem::Image((int)$item["itemID"], get_class($items), 128, -1) . "'>";
                echo "</a>";
                echo "<div class='item product'>" . implode("<BR>", array_map($e, explode("//", (string)$item["product"]))) . "</div>";
                echo "<div class='item qty'>" . (int)$item["qty"] . "</div>";
                echo "<div class='item price'>" . formatPrice($item["price"]) . "</div>";
                echo "<div class='item amount'>" . formatPrice($item["qty"] * $item["price"]) . "</div>";
                echo "</div>";
            }
            echo "</div></div>";

            $deliveryPrice = (float)$order["delivery_price"];
            echo "<div class='group'>";
            echo "<div class='item products_total'><label>" . tr("Продукти общо") . "</label><span>" . formatPrice($order["total"] - max($deliveryPrice, 0)) . "</span></div>";
            if ($deliveryPrice > 0) $deliveryText = formatPrice($deliveryPrice);
            else if ($deliveryPrice == 0) $deliveryText = tr("Безплатна");
            else $deliveryText = tr("Според тарифния план на куриера");
            echo "<div class='item delivey_price'><label>" . tr("Доставка") . "</label><span>$deliveryText</span></div>";
            echo "<div class='item order_total'><label>" . tr("Поръчка общо") . "</label><span>" . formatPrice($order["total"]) . "</span></div>";
            echo "</div>";

            echo "<div class='group'><div class='item order_note'><label>" . tr("Бележка към поръчката") . "</label><span>" . nl2br($e($order["note"])) . "</span></div></div>";
            echo "</div>";
        });
    }

    protected function getContentTitle(): string
    {
        return tr("Order Details");
    }
}
