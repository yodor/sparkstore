<?php
/**
 * A fast order - to the shop (FastOrderAdminMailer): fullname, phone, address, items (title, url, photo, price_html,
 * qty, options - name => value)
 * @var MailView $this
 */
echo $this->text(tr("A fast order was made at %s - call the client to agree the details.", $this->siteTitle()));
$phone = (string)$this->get("phone");
echo $this->details(array(
    tr("Name") => $this->e($this->get("fullname")),
    tr("Phone") => ($phone !== "") ? $this->link("tel:" . preg_replace("/[^0-9+]/", "", $phone), $phone) : "",
    tr("Address") => nl2br($this->e($this->get("address")), false),
));
echo $this->heading(tr("Products"));
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 16px 0;font-size:14px;">
<?php foreach ((array)$this->get("items") as $item) { ?>
<tr>
<td width="80" style="padding:8px 12px 8px 0;border-bottom:1px solid #e5e8eb;vertical-align:top;"><?php
    if ((string)$item["photo"] !== "") echo "<img src='" . $this->e($item["photo"]) . "' width='72' alt='' style='display:block;width:72px;height:auto;border:0;'>"; ?></td>
<td style="padding:8px 0;border-bottom:1px solid #e5e8eb;vertical-align:top;">
<?php echo $this->link((string)$item["url"], (string)$item["title"]); ?><br>
<?php foreach ((array)$item["options"] as $name => $value) echo "<span style='font-size:13px;color:#6b7280;'>" . $this->e($name) . ": " . $this->e($value) . "</span><br>"; ?>
<?php echo $this->e(tr("Quantity")) . ": " . (int)$item["qty"] . " &middot; " . $item["price_html"]; ?>
</td>
</tr>
<?php } ?>
</table>
