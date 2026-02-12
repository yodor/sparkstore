<?php
include_once("forms/InputForm.php");

class VariantPriceForm extends InputForm {

    public function __construct()
    {
        parent::__construct();

        $input = DataInputFactory::Create(InputType::SELECT, "variant_name", "Опция име", 1);
        $this->addInput($input);

        $input = DataInputFactory::Create(InputType::SELECT, "variant_value", "Стойност", 1);

        $this->addInput($input);
//
//        $input = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "photo", "Снимки", 0);
//        $this->addInput($input);

    }
}