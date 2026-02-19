<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("input/processors/DataObjectInput.php");
include_once("objects/data/GTMConvParam.php");
include_once("objects/data/GTMCommand.php");

class MarketingConfigForm extends InputForm
{


    public function __construct()
    {

        parent::__construct();

        $grp_basic = new InputGroup("basic", "Basic");
        $grp_basic->setDescription("Various Google and FB tracking IDs");
        $this->addGroup($grp_basic);

        $field = DataInputFactory::Create(InputType::TEXT, "googleID_analytics", "Google Analytics ID (eg: UA-123456789-1)", 0);
        $this->addInput($field, $grp_basic);

        $field = DataInputFactory::Create(InputType::TEXT, "googleID_ads", "Google ADs ID (eg: AW-123456789)", 0);
        $this->addInput($field, $grp_basic);

        $field = DataInputFactory::Create(InputType::TEXT, "googleID_ads_conversion", "Google ADs Any Page Conversion ID", 0);
        $this->addInput($field, $grp_basic);

        $field = DataInputFactory::Create(InputType::TEXT, "facebookID_pixel", "Facebook Pixel ID", 0);
        $this->addInput($field, $grp_basic);



        $grp_conversion = new InputGroup("conversion", "Google ADs Conversion IDs");
        $grp_conversion->setDescription("Format: AW-CONVERSION_ID/CONVERSION_LABEL");
        $this->addGroup($grp_conversion);

        $field = DataInputFactory::Create(InputType::TEXT, GTMConvParam::CART_ADD->value, tr("Product Add To Cart"), 0);
        $this->addInput($field, $grp_conversion);

        $field = DataInputFactory::Create(InputType::TEXT, GTMConvParam::FAST_ORDER->value, tr("Product Fast Order"), 0);
        $this->addInput($field, $grp_conversion);

        $field = DataInputFactory::Create(InputType::TEXT, GTMConvParam::PHONE_CALL->value, tr("Product Phone Call"), 0);
        $this->addInput($field, $grp_conversion);

        $field = DataInputFactory::Create(InputType::TEXT, GTMConvParam::QUERY_PRODUCT->value, tr("Product Query"), 0);
        $this->addInput($field, $grp_conversion);

        $field = DataInputFactory::Create(InputType::TEXT, GTMConvParam::CONTACT_REQUEST->value, tr("Contact Request"), 0);
        $this->addInput($field, $grp_conversion);

        $field = DataInputFactory::Create(InputType::TEXT, GTMConvParam::VIEW_PDP->value, tr("Any Product Page View (PDP)"), 0);
        $this->addInput($field, $grp_conversion);

        $field = DataInputFactory::Create(InputType::TEXT, GTMConvParam::VIEW_PLP->value, tr("Any Category Page View (PLP)"), 0);
        $this->addInput($field, $grp_conversion);

    }

}