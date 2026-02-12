<?php
include_once("forms/InputForm.php");

class QueryProductForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "fullname", "Име", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Телефон", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::EMAIL, "email", "Email", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "query", "Запитване", 1);
        $this->addInput($field);

    }


}