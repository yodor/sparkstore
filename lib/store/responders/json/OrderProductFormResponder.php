<?php
include_once("responders/json/JSONFormResponder.php");
include_once("store/utils/SellableItem.php");
include_once("store/forms/FastOrderProductForm.php");
include_once("store/mailers/FastOrderAdminMailer.php");

class OrderProductFormResponder extends JSONFormResponder
{

    /**
     * @var SellableItem
     */
    protected SellableItem $sellable;

    public function __construct(SellableItem $sellable)
    {
        parent::__construct();
        $this->sellable = $sellable;
    }

    protected function createForm(): InputForm
    {
        return new FastOrderProductForm();
    }

    public function _render(JSONResponse $resp): void
    {
        //echo "<pre>".print_r($_REQUEST, true)."</pre>";
        parent::_render($resp);
    }

    protected function onProcessSuccess(JSONResponse $resp): void
    {
        parent::onProcessSuccess($resp);

        //check posted variant
        $option_names = $this->sellable->getVariantNames();

        if (count($option_names)>0) {
            if (!isset($_REQUEST["variant"])) throw new Exception("Variant data not passed");
            $variant = Spark::SanitizeInput($_REQUEST["variant"]);

            foreach ($variant as $idx=>$pair) {
                list($option_name, $option_value) = explode(":", $pair);
                $option_name = Spark::SanitizeInput($option_name);
                $option_value = Spark::SanitizeInput($option_value);

                if (!$this->sellable->haveVariant($option_name)) throw new Exception("Incorrect variant name received");
                $vitem = $this->sellable->getVariant($option_name);
                if (!$vitem->haveParameter($option_value)) throw new Exception("Incorrect variant parameter received");
                $vitem->setSelected($option_value);
            }
        }

        $mailer = new FastOrderAdminMailer($this->form, $this->sellable);
        $mailer->send();

        $resp->message = tr("Поръчката Ви беше приета");

    }
}
?>