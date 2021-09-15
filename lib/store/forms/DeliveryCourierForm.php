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

        $field = new DataInput("delivery_courier", "Изберете куриер за доставка", 1);

        $radio = new RadioField($field);
        $radio->setIterator($data);
        $radio->getItemRenderer()->setValueKey(ArrayDataIterator::KEY_ID);
        $radio->getItemRenderer()->setLabelKey(ArrayDataIterator::KEY_VALUE);

        $field->setValidator(new EmptyValueValidator());
        new InputProcessor($field);

        $this->addInput($field);

    }

}

?>
