<?php
include_once("forms/InputForm.php");

class EkontOfficeInputForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXTAREA, "office", "Избран офис", 1);
        //$field->getRenderer()->input()?->setAttribute("readonly", "1");
        $this->addInput($field);

    }


}