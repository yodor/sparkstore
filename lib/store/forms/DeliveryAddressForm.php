<?php
include_once("forms/InputForm.php");
include_once("iterators/ArrayDataIterator.php");

class DeliveryAddressForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $cart = Cart::Instance();
        $options = $cart->getDelivery()->getSelectedCourier()->getOptions();

        $option_values = array();
        foreach ($options as $id=>$option)  {
            $option_values[$id] = $option->getTitle();
        }

        $data = new ArrayDataIterator($option_values);

        $field = DataInputFactory::Create(InputType::RADIO, "delivery_option", "Изберете адрес за доставка", 1);
        $radio = $field->getRenderer();
        $radio->setIterator($data);
        $radio->getItemRenderer()->setValueKey(ArrayDataIterator::KEY_ID);
        $radio->getItemRenderer()->setLabelKey(ArrayDataIterator::KEY_VALUE);

        $this->addInput($field);

    }

}