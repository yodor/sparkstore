<?php
/**
 * The activation of a new profile - to the client (RegisterCustomerActivationMailer): fullname, link, code
 * @var MailView $this
 */
echo $this->text(tr("Hello") . ", " . $this->get("fullname"));
echo $this->text(tr("Your registration at %s is complete.", $this->siteTitle()));
echo $this->text(tr("To activate your profile, open the activation link below."));
echo $this->button((string)$this->get("link"), tr("Activate the profile"));
echo $this->paragraph("<span style='font-size:13px;color:#6b7280;'>" . $this->e(tr("If the button does not open, copy this address into your browser:")) . "<br>"
    . $this->link((string)$this->get("link")) . "</span>");
echo $this->paragraph($this->e(tr("Activation code")) . ": <strong style='font-family:Consolas,Menlo,monospace;'>" . $this->e($this->get("code")) . "</strong>");
echo $this->text(tr("Sincerely") . ",\n" . $this->siteTitle());
