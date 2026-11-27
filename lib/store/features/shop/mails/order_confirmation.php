<?php
/**
 * The order confirmation to the client (OrderConfirmationMailer - OrderMailData)
 * @var MailView $this
 */
$client = (array)$this->get("client");
echo $this->text(tr("Hello") . ", " . ($client["fullname"] ?? ""));
echo $this->text(tr("Thank you for your order! We received it and it is being processed - we will contact you if anything needs to be clarified."));
echo $this->button((string)$this->get("account_url"), tr("View your order"));
echo $this->part("order_summary");
echo $this->text(tr("Sincerely") . ",\n" . $this->siteTitle());
