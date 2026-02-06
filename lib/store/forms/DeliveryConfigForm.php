<?php
include_once("forms/InputForm.php");
include_once("input/validators/NumericValidator.php");
include_once("iterators/ArrayDataIterator.php");
include_once("store/utils/cart/Cart.php");

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

            $group = new InputGroup("courier_$courierID", "Куриер: {$courier->getTitle()}");
            $this->addGroup($group);

            $aw1 = new ArrayDataIterator(array(1=>"Разрешени", 0=>"Забранени"));

            $field = DataInputFactory::Create(
                InputType::RADIO,
                $delivery->configPrefix($courierID, "enabled"),
                tr("Доставки"),
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
                    tr("Опция")." - ".$option->getTitle(),
                    0);
                $this->addInput($field, $group);

                $field = DataInputFactory::Create(
                    InputType::TEXT, $courier->configPrefix($optionID, "price"),
                    tr("Цена"),
                    1);

                $field->setValidator(new NumericValidator(true,true));
                // 	    $field->getRenderer()->addon_content = "лв.";
                $this->addInput($field, $group);
            }

        }

    }

}

?>
