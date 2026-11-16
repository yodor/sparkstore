<?php
include_once("responders/json/ContactRequestFormResponder.php");
include_once("beans/ConfigBean.php");
include_once("objects/data/GTMConvParam.php");

/**
 * Contact enquiry of a store site (contacts page): the core enquiry dialog sent to StoreConfig::ORDER_EMAIL -
 * the shop's address from config/store.php or the admin (Content → Config → Base 'email_orders') - with the
 * marketing conversion of the store's marketing config. Nothing is stored.
 */
class StoreContactFormResponder extends ContactRequestFormResponder
{
    /**
     * Marketing conversion (MarketingConfigForm key) sent after the enquiry was sent
     * @var GTMConvParam
     */
    protected GTMConvParam $conversionParam = GTMConvParam::CONTACT_REQUEST;

    protected function recipient(): string
    {
        return (string)Spark::Get(StoreConfig::ORDER_EMAIL);
    }

    protected function conversion(): ?GTMConversionCommand
    {
        $config = ConfigBean::Factory();
        $config->setSection("marketing_config");
        $conversionID = $config->get($this->conversionParam->value);
        return $conversionID ? new GTMConversionCommand($conversionID) : null;
    }
}
