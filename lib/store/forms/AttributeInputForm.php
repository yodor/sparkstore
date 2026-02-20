<?php
include_once("forms/InputForm.php");

class AttributeInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "name", "Name", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT,"unit", "Unit", 0);
        $this->addInput($field);
    }

}