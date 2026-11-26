<?php
/**
 * A gift voucher request - to the shop (VoucherAdminMailer): rcpt_name, name, phone, amount, note
 * @var MailView $this
 */
echo $this->text(tr("A gift voucher was requested at %s.", $this->siteTitle()));
$phone = (string)$this->get("phone");
echo $this->details(array(
    tr("Name of the recipient") => $this->e($this->get("rcpt_name")),
    tr("Requested by") => $this->e($this->get("name")),
    tr("Phone") => ($phone !== "") ? $this->link("tel:" . preg_replace("/[^0-9+]/", "", $phone), $phone) : "",
    tr("Amount") => $this->e($this->get("amount")),
    tr("Note") => nl2br($this->e($this->get("note")), false),
));
