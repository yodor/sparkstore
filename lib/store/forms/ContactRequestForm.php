<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class ContactRequestForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "fullname", "Име", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::EMAIL, "email", "Email", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "query", "Запитване", 1);
        $this->addInput($field);

    }

}

?>
