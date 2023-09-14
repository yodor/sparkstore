<?php

include_once("responders/json/JSONFormResponder.php");
include_once("store/forms/VoucherForm.php");
include_once("iterators/ArrayDataIterator.php");
include_once("input/validators/NumericValidator.php");
include_once("store/mailers/VoucherAdminMailer.php");

class VoucherFormResponder extends JSONFormResponder
{

    public function __construct()
    {
        parent::__construct("VoucherFormResponder");

    }

    protected function createForm(): InputForm
    {
        return new VoucherForm();
    }

    protected function onProcessSuccess(JSONResponse $resp)
    {
        parent::onProcessSuccess($resp);

        if ($this->form instanceof VoucherForm) {
            $mailer = new VoucherAdminMailer($this->form);
            $mailer->send();
        }

        $resp->message = tr("Заявката Ви беще приета");
    }
}
?>