<?php
include_once("forms/MarketingConfigForm.php");

/**
 * The marketing settings of a store (Settings > Marketing): the core tracking IDs and contact conversion, and the
 * Google Ads conversions of the products
 */
class StoreMarketingConfigForm extends MarketingConfigForm
{
    public function __construct()
    {
        parent::__construct();
        $this->addConversion(GTMConvParam::CART_ADD, tr("Product Add To Cart"));
        $this->addConversion(GTMConvParam::FAST_ORDER, tr("Product Fast Order"));
        $this->addConversion(GTMConvParam::PHONE_CALL, tr("Product Phone Call"));
        $this->addConversion(GTMConvParam::QUERY_PRODUCT, tr("Product Query"));
        $this->addConversion(GTMConvParam::VIEW_PDP, tr("Any Product Page View (PDP)"));
        $this->addConversion(GTMConvParam::VIEW_PLP, tr("Any Category Page View (PLP)"));
    }
}
