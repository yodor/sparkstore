<?php
include_once("components/renderers/cells/TableCell.php");
include_once("store/beans/OrderItemsBean.php");

class OrderItemsCell extends TableCell
{

    protected $orderID = -1;

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->orderID = $data["orderID"];

    }

    protected function renderImpl(): void
    {

        global $order_items;

        $qry = $order_items->queryField("orderID", $this->orderID);
        $qry->select->order_by = " position ASC ";
        $qry->exec();

        echo "<div class='group order_items'>";

        while ($item = $qry->next()) {

            $piID = $item["piID"];
            $prodID = $item["prodID"];

            $itemID = $item["itemID"];

            echo "<div class='item qty'>";
            echo "<label>" . tr("Позиция") . "</label>";
            echo "<span>" . $item["position"] . "</span>";
            echo "</div>";

            echo "<div class='item photo'>";
            echo "<label>";
            echo StorageItem::Image($itemID, get_class($order_items), 100, 100);
            echo "</label>";

            echo "</div>";

            $details = explode("//", $item["product"]);
            foreach ($details as $index => $data) {
                $label_value = explode("||", $data);

                echo "<div class='item'>";
                echo "<label>" . tr($label_value[0]) . "</label>";
                echo "<span>" . $label_value[1] . "</span>";
                echo "</div>";
            }

            echo "<div class='item qty'>";
            echo "<label>" . tr("Количество") . "</label>";
            echo "<span>" . $item["qty"] . "</span>";
            echo "</div>";

            echo "<div class='item price'>";
            echo "<label>" . tr("Цена") . "</label>";
            echo "<span>" . sprintf("%0.2f лв.", $item["price"]) . "</span>";
            echo "</div>";

            echo "<div class='item price'>";
            echo "<label>" . tr("Цена общо") . "</label>";
            echo "<span>" . sprintf("%0.2f лв.", ($item["qty"] * $item["price"])) . "</span>";
            echo "</div>";

            if ($prodID > 0) {
                echo "<a class='Action' href='" . Spark::Get(Config::LOCAL) . "/admin/store/products/inventory/list.php?prodID=$prodID'>" . tr("Виж инвентар") . "</a>";
            }
        }

        echo "</div>";

    }

}

?>
