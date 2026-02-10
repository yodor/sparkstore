<?php
include_once("responders/json/JSONFormResponder.php");
include_once("store/forms/VariantPriceForm.php");
include_once("store/beans/ProductVariantsBean.php");


class VariantPriceFormResponder extends JSONFormResponder
{

    protected int $prodID = -1;

    public function __construct(int $prodID)
    {
        $this->prodID = $prodID;
        parent::__construct();
    }

    protected function createForm(): InputForm
    {
        return new VariantPriceForm();
    }

    public function _render(JSONResponse $resp): void
    {
        $this->form->getRenderer()->render();
    }

    protected function onProcessSuccess(JSONResponse $resp): void
    {
        parent::onProcessSuccess($resp);


        $resp->message = tr("Заявката Ви беще приета");
    }

}
?>