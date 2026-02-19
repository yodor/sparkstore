<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("objects/data/GTAGObject.php");
include_once("input/processors/DataObjectInput.php");
include_once("objects/data/GTMConvParam.php");

class MarketingConfigForm extends InputForm
{


    public function __construct()
    {

        parent::__construct();

        $grp_conversion = new InputGroup("conversion", "Conversion Tags");
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