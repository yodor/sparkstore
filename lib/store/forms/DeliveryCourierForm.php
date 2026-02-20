<?php
include_once("forms/InputForm.php");
include_once("iterators/ArrayDataIterator.php");

class DeliveryCourierForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $cart = Cart::Instance();
        $couriers = $cart->getDelivery()->getCouriers();

        $option_values = array();
        foreach ($couriers as $id=>$courier)  {
            $option_values[$id] = $courier->getTitle();
        }

        $data = new ArrayDataIterator($option_values);

        $field = DataInputFactory::Create(InputType::RADIO, "delivery_courier", "Изберете куриер за доставка", 1);

        $radio = $field->getRenderer();
        $radio->setIterator($data);
        $radio->getItemRenderer()->setValueKey(ArrayDataIterator::KEY_ID);
        $radio->getItemRenderer()->setLabelKey(ArrayDataIterator::KEY_VALUE);

        $this->addInput($field);

    }

}