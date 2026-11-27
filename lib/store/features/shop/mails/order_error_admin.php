<?php
/**
 * An order could not be finalized - to the shop (OrderErrorAdminMailer): error
 * @var MailView $this
 */
echo $this->text(tr("An order could not be finalized at %s.", $this->siteTitle()));
echo $this->paragraph("<span style='font-family:Consolas,Menlo,monospace;font-size:13px;white-space:pre-wrap;'>" . $this->e($this->get("error")) . "</span>");
