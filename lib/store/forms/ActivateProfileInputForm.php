<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class ActivateProfileInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::EMAIL, "email", "Email", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "confirm_code", "Активационен код", 1);
        $this->addInput($field);

    }

}

?>
