<?php
/**
 * A product is in stock again - to the client who asked to be notified (InstockProductMailer): product, url
 * @var MailView $this
 */
echo $this->text(tr("Hello") . ",");
echo $this->text(tr("The product you were looking for is in stock again:"));
echo $this->paragraph("<strong>" . $this->link((string)$this->get("url"), (string)$this->get("product")) . "</strong>");
echo $this->button((string)$this->get("url"), tr("View the product"));
echo $this->text(tr("Sincerely") . ",\n" . $this->siteTitle());
