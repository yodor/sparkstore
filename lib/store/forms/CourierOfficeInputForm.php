<?php
include_once("forms/InputForm.php");

class CourierOfficeInputForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXTAREA, "office", "Въведете адрес на офис за доставка", 1);
        //$field->getRenderer()->input()?->setAttribute("readonly", "1");
        $this->addInput($field);

    }


}