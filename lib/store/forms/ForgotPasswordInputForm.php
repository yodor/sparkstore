<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class ForgotPasswordInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "email", "Email", 1);
        $this->addInput($field);

    }

}

?>