<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class VariantOptionInputForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "option_name", "Option Name", 1);
        $this->addInput($field);
        $field->enableTranslator(TRUE);

    }

}

?>