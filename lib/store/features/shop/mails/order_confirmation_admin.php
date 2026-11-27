<?php
/**
 * A new order - to the shop (OrderConfirmationAdminMailer - OrderMailData)
 * @var MailView $this
 */
$client = (array)$this->get("client");
echo $this->text(tr("A new order was made at %s.", $this->siteTitle()));
echo $this->details(array(
    tr("Client") => $this->e($client["fullname"] ?? ""),
    tr("Email") => ($client["email"] ?? "") !== "" ? $this->link("mailto:" . $client["email"], (string)$client["email"]) : "",
    tr("Phone") => ($client["phone"] ?? "") !== "" ? $this->link("tel:" . preg_replace("/[^0-9+]/", "", (string)$client["phone"]), (string)$client["phone"]) : "",
));
echo $this->button((string)$this->get("admin_url"), tr("Open the order in the admin"));
echo $this->part("order_summary");
echo $this->paragraph("<strong>" . $this->e(tr("Confirm the order in the admin (Orders) after it is processed.")) . "</strong>");
