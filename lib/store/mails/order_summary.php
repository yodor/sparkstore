<?php
/**
 * The summary of an order (a part of the order mails - OrderMailData): the delivery, the items, the totals, the note
 * @var MailView $this
 */
$border = "border-bottom:1px solid #e5e8eb;";
echo $this->details(array(
    tr("Order number") => $this->e($this->get("orderID")),
    tr("Date") => $this->e($this->get("date")),
    tr("Courier") => $this->e($this->get("courier")),
    tr("Delivery method") => $this->e($this->get("option")),
    tr("Delivery address") => $this->get("address_html"),
    tr("Invoicing") => $this->get("invoice") ? $this->e(tr("Yes")) : "",
));
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 16px 0;font-size:14px;">
<tr>
<th align="left" style="padding:6px 8px 6px 0;<?php echo $border; ?>color:#6b7280;font-weight:normal;"><?php echo $this->e(tr("Product")); ?></th>
<th align="center" style="padding:6px 8px;<?php echo $border; ?>color:#6b7280;font-weight:normal;"><?php echo $this->e(tr("Quantity")); ?></th>
<th align="right" style="padding:6px 8px;<?php echo $border; ?>color:#6b7280;font-weight:normal;white-space:nowrap;"><?php echo $this->e(tr("Unit price")); ?></th>
<th align="right" style="padding:6px 0 6px 8px;<?php echo $border; ?>color:#6b7280;font-weight:normal;"><?php echo $this->e(tr("Amount")); ?></th>
</tr>
<?php foreach ((array)$this->get("items") as $item) {
    $lines = (array)$item["lines"];
    $name = array_shift($lines);
    ?>
<tr>
<td style="padding:8px 8px 8px 0;<?php echo $border; ?>vertical-align:top;"><?php echo $this->link((string)$item["url"], (string)$name);
    foreach ($lines as $line) echo "<br><span style='font-size:13px;color:#6b7280;'>" . $this->e($line) . "</span>"; ?></td>
<td align="center" style="padding:8px;<?php echo $border; ?>vertical-align:top;"><?php echo (int)$item["qty"]; ?></td>
<td align="right" style="padding:8px;<?php echo $border; ?>vertical-align:top;white-space:nowrap;"><?php echo $item["price_html"]; ?></td>
<td align="right" style="padding:8px 0 8px 8px;<?php echo $border; ?>vertical-align:top;white-space:nowrap;"><?php echo $item["amount_html"]; ?></td>
</tr>
<?php } ?>
</table>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="right" style="margin:0 0 16px 0;font-size:14px;">
<tr><td style="padding:3px 16px 3px 0;color:#6b7280;"><?php echo $this->e(tr("Products total")); ?>:</td><td align="right" style="padding:3px 0;white-space:nowrap;"><?php echo $this->get("products_total_html"); ?></td></tr>
<?php if ($this->get("discount_html") !== "") { ?>
<tr><td style="padding:3px 16px 3px 0;color:#6b7280;"><?php echo $this->e(tr("Discounts")); ?>:</td><td align="right" style="padding:3px 0;white-space:nowrap;">-<?php echo $this->get("discount_html"); ?></td></tr>
<?php } ?>
<tr><td style="padding:3px 16px 3px 0;color:#6b7280;"><?php echo $this->e(tr("Delivery")); ?>:</td><td align="right" style="padding:3px 0;white-space:nowrap;"><?php echo $this->get("delivery_html"); ?></td></tr>
<tr><td style="padding:6px 16px 3px 0;font-weight:bold;"><?php echo $this->e(tr("Order total")); ?>:</td><td align="right" style="padding:6px 0 3px 0;font-weight:bold;white-space:nowrap;"><?php echo $this->get("total_html"); ?></td></tr>
</table>
<div style="clear:both;"></div>
<?php
if ($this->get("note") !== "") {
    echo $this->heading(tr("Order note"));
    echo $this->text((string)$this->get("note"));
}
