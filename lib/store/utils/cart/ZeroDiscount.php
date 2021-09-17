<?php
include_once("store/utils/cart/IDiscountProcessor.php");

class ZeroDiscount implements IDiscountProcessor
{
    public function initialize()
    {

    }

    public function label() : string {
        return "No Discount";
    }

    public function description() : string {
        return "No Discount";
    }

    public function amount(): float
    {
        return 0.0;
    }

    public function calculate()
    {
        // TODO: Implement calculate() method.
    }
}

?>