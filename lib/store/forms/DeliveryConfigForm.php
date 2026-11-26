<?php
include_once("forms/InputForm.php");
include_once("input/validators/NumericValidator.php");
include_once("iterators/ArrayDataIterator.php");
include_once("store/utils/cart/Cart.php");
include_once("store/utils/cart/DeliveryPolicy.php");
include_once("store/courier/Couriers.php");

class DeliveryConfigForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();



        //do not initialize
        $delivery = new Delivery();

        $couriers = $delivery->getCouriers();

        foreach ($couriers as $courierID=>$courier) {
            if (!$courier instanceof DeliveryCourier) continue;

            $group = new InputGroup("courier_$courierID", tr("Courier") . ": " . $courier->getTitle());
            $this->addGroup($group);

            $aw1 = new ArrayDataIterator(array(1=>tr("Enabled"), 0=>tr("Disabled")));

            $field = DataInputFactory::Create(
                InputType::RADIO,
                $delivery->configPrefix($courierID, "enabled"),
                tr("Deliveries"),
                1);
            $field->getRenderer()->setIterator($aw1);
            $field->getRenderer()->getItemRenderer()->setValueKey(ArrayDataIterator::KEY_ID);
            $field->getRenderer()->getItemRenderer()->setLabelKey(ArrayDataIterator::KEY_VALUE);

            $this->addInput($field, $group);

            $options = $courier->getOptions();

            foreach ($options as $optionID=>$option) {
                if (!$option instanceof DeliveryOption) continue;

                $field = DataInputFactory::Create(
                    InputType::CHECKBOX, $courier->configPrefix($optionID, "enabled"),
                    tr("Option")." - ".$option->getTitle(),
                    0);
                $this->addInput($field, $group);

                $field = DataInputFactory::Create(
                    InputType::TEXT, $courier->configPrefix($optionID, "price"),
                    tr("Price"),
                    1);

                $field->setValidator(new NumericValidator(true,true));
                $this->addInput($field, $group);
            }

            //the courier's own settings (an API account)
            Couriers::Get((int)$courierID)?->settings($this, $group);

        }

        //the delivery and return terms for the search engines (DeliveryPolicy) - as the terms page says them
        $group = new InputGroup("policy", tr("Terms for the search engines (Google): delivery and returns"));
        $this->addGroup($group);

        $field = DataInputFactory::Create(InputType::TEXT, DeliveryPolicy::KEY_COUNTRY, tr("Delivery country (code, e.g. BG)"), 1);
        $field->setValue(DeliveryPolicy::DEFAULT_COUNTRY);
        $this->addInput($field, $group);

        $field = DataInputFactory::Create(InputType::TEXT, DeliveryPolicy::KEY_DAYS_MIN, tr("Delivery time - from (working days)"), 1);
        $field->setValue((string)DeliveryPolicy::DEFAULT_DAYS_MIN);
        $field->setValidator(new NumericValidator(false, false));
        $this->addInput($field, $group);

        $field = DataInputFactory::Create(InputType::TEXT, DeliveryPolicy::KEY_DAYS_MAX, tr("Delivery time - to (working days)"), 1);
        $field->setValue((string)DeliveryPolicy::DEFAULT_DAYS_MAX);
        $field->setValidator(new NumericValidator(false, false));
        $this->addInput($field, $group);

        $field = DataInputFactory::Create(InputType::TEXT, DeliveryPolicy::KEY_RETURN_DAYS, tr("Return period (days from the receipt, 0 - no returns)"), 1);
        $field->setValue((string)DeliveryPolicy::DEFAULT_RETURN_DAYS);
        $field->setValidator(new NumericValidator(true, false));
        $this->addInput($field, $group);

        $field = DataInputFactory::Create(InputType::CHECKBOX, DeliveryPolicy::KEY_FREE_RETURN, tr("Free returns (the store pays the transport)"), 0);
        $this->addInput($field, $group);
    }

}