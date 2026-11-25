<?php
include_once("utils/ImageSizes.php");
include_once("components/Container.php");
include_once("components/LabelSpan.php");
include_once("components/Image.php");
include_once("storage/StorageItem.php");
include_once("store/beans/OrdersBean.php");
include_once("store/beans/OrderItemsBean.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/forms/InvoiceDetailsInputForm.php");
include_once("store/utils/cart/Cart.php");
include_once("store/utils/url/ProductURL.php");

/**
 * Order details shown in the admin (order details page) and to the client (account order details):
 * summary (number, date, status, delivery, invoicing), items, totals and the client's note.
 * Text entered by the client (address, note, product lines) is escaped.
 */
class OrderView extends Container
{
    protected ?array $order = null;

    /**
     * Show the invoice details of the client (admin) - the client's own page shows only yes / no
     * @var bool
     */
    protected bool $invoiceDetailsEnabled = false;

    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("OrderView");
    }

    public function setInvoiceDetailsEnabled(bool $mode) : void
    {
        $this->invoiceDetailsEnabled = $mode;
    }

    /**
     * Load order '$orderID' - with '$userID' only an order of that client
     * @param int $orderID
     * @param int $userID -1: any client (admin)
     * @return bool false if the order was not found
     * @throws Exception
     */
    public function setOrder(int $orderID, int $userID = -1) : bool
    {
        $orders = new OrdersBean();
        $query = $orders->queryFull();
        $query->stmt->where()->match("orderID", $orderID);
        if ($userID > 0) $query->stmt->where()->match("userID", $userID);
        $query->stmt->limit(1);
        $query->exec();
        $order = $query->next();
        $query->free();

        $this->order = $order ?: null;
        $this->items()->clear();
        if (is_null($this->order)) return false;

        $this->items()->append($this->createSummary());
        $this->items()->append($this->createItems());
        $this->items()->append($this->createTotals());
        $this->items()->append($this->createNote());
        return true;
    }

    public function getOrder() : ?array
    {
        return $this->order;
    }

    protected function group(string $className) : Container
    {
        $group = new Container(false);
        $group->setComponentClass("group " . $className);
        return $group;
    }

    /**
     * A labelled value: <div class='item <name>'><span class='label'>..</span><span>..</span></div>
     * @param Container $group
     * @param string $name
     * @param string $label translated with tr()
     * @return Component the value - setText() for data, setContents() for prepared HTML
     */
    protected function field(Container $group, string $name, string $label) : Component
    {
        $field = new LabelSpan();
        $field->setComponentClass("item " . $name);
        $field->label()->setText(tr($label));
        $group->items()->append($field);
        $this->lastField = $field;
        return $field->span();
    }

    /**
     * The LabelSpan created by the last field() call - for appending more components to it
     * @var LabelSpan|null
     */
    protected ?LabelSpan $lastField = null;

    protected function createSummary() : Container
    {
        $order = $this->order;
        $group = $this->group("summary");

        $this->field($group, "order_num", "Order number")->setText((string)$order["orderID"]);
        $this->field($group, "order_date", "Date")->setText((string)$order["order_date"]);
        $this->field($group, "status", "Status")->setText(tr((string)$order["status"]));

        $delivery = new Delivery();
        $delivery->setSelectedCourier($order["delivery_courier"]);
        $courier = $delivery->getSelectedCourier();
        $courier->setSelectedOption($order["delivery_option"]);
        $option = $courier->getSelectedOption();

        $this->field($group, "delivery_courier", "Courier")->setText($courier->getTitle());
        $this->field($group, "delivery_option", "Delivery method")->setText($option->getTitle());

        $address = $this->field($group, "delivery_address", "Delivery address");
        if ($option->getID() == DeliveryOption::USER_ADDRESS) {
            try {
                $form = new ClientAddressInputForm();
                $form->unserializeXML((string)$order["delivery_address"]);
                $this->lastField->items()->append($form->CreatePlainRenderer());
            }
            catch (Exception $e) {
                $address->setText($e->getMessage());
            }
        }
        else if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
            $address->setComponentClass("ClientAddressList");
            $address->setContents(nl2br(Spark::AttributeValue((string)$order["delivery_address"])));
        }

        $invoice = $this->field($group, "require_invoice", "Invoicing");
        $invoice->setText(($order["require_invoice"] > 0) ? tr("Yes") : tr("No"));
        if ($this->invoiceDetailsEnabled && $order["require_invoice"] > 0) {
            $invoices = new InvoiceDetailsBean();
            //none when the client deleted the invoice details since the order
            $result = $invoices->getResult("userID", (string)(int)$order["userID"], $invoices->key());
            if (is_array($result)) {
                $form = new InvoiceDetailsInputForm();
                $form->loadBeanData($result[$invoices->key()], $invoices);
                $this->lastField->items()->append($form->CreatePlainRenderer());
            }
            else {
                Debug::ErrorLog("Invoice details of order {$order["orderID"]}: none for the client");
            }
        }

        return $group;
    }

    protected function createItems() : Container
    {
        $group = $this->group("order_items");
        $viewport = new Container(false);
        $viewport->setComponentClass("viewport");
        $group->items()->append($viewport);

        $heading = new Container(false);
        $heading->setComponentClass("line heading");
        foreach (array("Pos.", "", "Product", "Quantity", "Unit price", "Amount") as $title) {
            $cell = new Component(false);
            $cell->setTagName("span");
            $cell->setComponentClass("");
            $cell->setText($title ? tr($title) : "");
            $heading->items()->append($cell);
        }
        $viewport->items()->append($heading);

        $items = new OrderItemsBean();
        $query = $items->queryField("orderID", (string)$this->order["orderID"]);
        $query->stmt->columns("prodID", "itemID", "price", "qty", "product");
        $query->stmt->order("position", OrderDirection::ASC);
        $query->exec();
        $position = 0;
        while ($item = $query->next()) {
            $position++;
            $viewport->items()->append($this->createItemLine($position, $item, get_class($items)));
        }
        $query->free();

        return $group;
    }

    protected function createItemLine(int $position, array $item, string $beanClass) : Container
    {
        $line = new Container(false);
        $line->setComponentClass("line");

        $this->lineCell($line, "pos")->setText((string)$position);

        $photo = new Container(false);
        $photo->setTagName("a");
        $photo->setComponentClass("item photo");
        $url = new ProductURL();
        $url->setProductID((int)$item["prodID"]);
        $photo->setAttribute("href", $url->fullURL()->toString());
        $image = new Image();
        $image->setStorageItem(new StorageItem((int)$item["itemID"], $beanClass));
        $image->setPhotoSize(ImageSize::Small);
        $photo->items()->append($image);
        $line->items()->append($photo);

        //product name and options - "name//option: value//..." as entered with the order
        $lines = array_map(fn($part) => Spark::AttributeValue($part), explode("//", (string)$item["product"]));
        $this->lineCell($line, "product")->setContents(implode("<BR>", $lines));

        $this->lineCell($line, "qty")->setText((string)(int)$item["qty"]);
        $this->lineCell($line, "price")->setContents(formatPrice($item["price"]));
        $this->lineCell($line, "amount")->setContents(formatPrice($item["qty"] * $item["price"]));
        return $line;
    }

    protected function lineCell(Container $line, string $name) : Component
    {
        $cell = new Component(false);
        $cell->setComponentClass("item " . $name);
        $line->items()->append($cell);
        return $cell;
    }

    protected function createTotals() : Container
    {
        $order = $this->order;
        $group = $this->group("total");

        $deliveryPrice = (float)$order["delivery_price"];
        $discount = (float)($order["discount_amount"] ?? 0);

        $this->field($group, "products_total", "Products total")->setContents(formatPrice($order["total"] - max($deliveryPrice, 0) + $discount));
        if ($discount > 0) {
            $this->field($group, "discount_amount", "Discounts")->setContents(formatPrice($discount));
        }
        //formatPrice() returns HTML (currency entity) - the other texts are escaped
        if ($deliveryPrice > 0) $deliveryText = formatPrice($deliveryPrice);
        else if ($deliveryPrice == 0) $deliveryText = Spark::AttributeValue(tr("Free"));
        else $deliveryText = Spark::AttributeValue(tr("By the tariff of the courier"));
        $this->field($group, "delivey_price", "Delivery")->setContents($deliveryText);
        $this->field($group, "order_total", "Order total")->setContents(formatPrice($order["total"]));

        return $group;
    }

    protected function createNote() : Container
    {
        $group = $this->group("note");
        $note = $this->field($group, "order_note", "Order note");
        $note->setContents(nl2br(Spark::AttributeValue((string)$this->order["note"])));
        $group->setRenderEnabled(trim((string)$this->order["note"]) !== "");
        return $group;
    }
}
