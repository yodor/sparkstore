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
        parent::__construct();

    }

    protected function createForm(): InputForm
    {
        return new VoucherForm();
    }

    protected function onProcessSuccess(JSONResponse $resp): void
    {
        parent::onProcessSuccess($resp);

        if ($this->form instanceof VoucherForm) {
            $mailer = new VoucherAdminMailer($this->form);
            //the request is only this mail
            if (!$mailer->send()) {
                throw new Exception(tr("The request was not sent. Please try again later or call us."));
            }
        }

        $resp->message = tr("Your request was accepted");
    }
}