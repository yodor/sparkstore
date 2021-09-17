<?php

interface IDiscountProcessor {

    public function initialize();
    public function calculate();
    public function label() : string;
    public function description() : string;
    public function amount() : float;

}
?>
