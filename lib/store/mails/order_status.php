<?php
/**
 * The new status of an order - to the client (OrderStatusMailer): orderID, status, fullname, account_url
 * @var MailView $this
 */
echo $this->text(tr("Hello") . ", " . $this->get("fullname"));
echo $this->paragraph($this->e(tr("The status of your order No %s was updated:", $this->get("orderID")))
    . " <strong>" . $this->e(tr((string)$this->get("status"))) . "</strong>");
echo $this->button((string)$this->get("account_url"), tr("View your order"));
echo $this->text(tr("Sincerely") . ",\n" . $this->siteTitle());
