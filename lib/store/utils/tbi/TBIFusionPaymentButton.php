<?php
include_once("store/utils/CreditPaymentButton.php");

class TBIFusionPaymentButton extends CreditPaymentButton
{
    protected Link $css;
    protected Script $script;

    public function __construct(SellableItem $item)
    {
        parent::__construct($item);

        $config = ConfigBean::Factory();
        $config->setSection("store_config");
        $tbi_fusion_style = $config->get("tbi_fusion_style");
        $tbi_fusion_script = $config->get("tbi_fusion_script");

        if ($tbi_fusion_style && $tbi_fusion_script) {
            $css = new Link();
            $css->setHref($tbi_fusion_style);
            $script = new Script();
            $script->setSrc($tbi_fusion_script);
            $script->setAttribute("async");
            $this->initialize($css, $script);
        }
    }

    public function initialize(Link $css, Script $script) : void
    {
        $this->css = $css;
        $this->script = $script;
        $this->enabled = true;
    }
    public function renderButton()
    {
        $this->css->render();
        $this->script->render();
    }
}