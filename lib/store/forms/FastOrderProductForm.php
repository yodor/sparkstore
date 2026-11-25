<?php
include_once("forms/InputForm.php");

class FastOrderProductForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "fullname", "Name", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Phone", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "address", "Delivery address", 1);
        $this->addInput($field);

    }

}