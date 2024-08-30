<?php
include_once("components/renderers/cells/TableCellRenderer.php");

class OrderInvoiceCellRenderer extends TableCellRenderer
{

    protected $userID = -1;
    protected $require_invoice = 0;

    protected function renderImpl()
    {

        if ($this->require_invoice > 0) {

            global $invoices;
            $invoice_details = $invoices->getByRef("userID", $this->userID);

            echo "<div class='group invoice_data'>";
            echo "<div class='item company_name'>";
            echo "<label>" . tr("Фирма") . "</label>";
            echo "<span>" . $invoice_details["company_name"] . "</span>";
            echo "</div>";
            echo "<div class='item acc_person'>";
            echo "<label>" . tr("МОЛ") . "</label>";
            echo "<span>" . $invoice_details["acc_person"] . "</span>";
            echo "</div>";
            echo "<div class='item city'>";
            echo "<label>" . tr("Град") . "</label>";
            echo "<span>" . $invoice_details["city"] . "</span>";
            echo "</div>";
            echo "<div class='item postcode'>";
            echo "<label>" . tr("Пощенски код") . "</label>";
            echo "<span>" . $invoice_details["postcode"] . "</span>";
            echo "</div>";
            echo "<div class='item address1'>";
            echo "<label>" . tr("Адрес1") . "</label>";
            echo "<span>" . $invoice_details["address1"] . "</span>";
            echo "</div>";
            echo "<div class='item address2'>";
            echo "<label>" . tr("Адрес2") . "</label>";
            echo "<span>" . $invoice_details["address2"] . "</span>";
            echo "</div>";
            echo "<div class='item vat'>";
            echo "<label>" . tr("ЕИК") . "</label>";
            echo "<span>" . $invoice_details["vat"] . "</span>";
            echo "</div>";
            echo "<div class='item vat_registered'>";
            echo "<label>" . tr("ДДС") . "</label>";
            echo "<span>" . ($invoice_details["vat_registered"] > 0 ? tr("Да") : tr("Не")) . "</span>";
            echo "</div>";
            echo "</div>";

        }
        else {
            echo tr("Без");
        }
    }

    public function setData(array $data) : void
    {
        parent::setData($data);

        $this->userID = (int)$data["userID"];
        $this->require_invoice = (int)$data["require_invoice"];

    }

}

?>
