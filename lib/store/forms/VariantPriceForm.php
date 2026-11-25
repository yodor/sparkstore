<?php
include_once("forms/InputForm.php");

class VariantPriceForm extends InputForm {

    public function __construct()
    {
        parent::__construct();

        $input = DataInputFactory::Create(InputType::SELECT, "variant_name", "Option Name", 1);
        $this->addInput($input);

        $input = DataInputFactory::Create(InputType::SELECT, "variant_value", "Value", 1);

        $this->addInput($input);
//
//        $input = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "photo", "Photos", 0);
//        $this->addInput($input);

    }
}