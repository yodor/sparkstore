<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("input/validators/SimpleTextValidator.php");
class ContactRequestForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "fullname", "Име", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::EMAIL, "email", "Email", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "query", "Запитване (до 255 символа)", 1);
        $field->getProcessor()->accepted_tags = "";
        $this->addInput($field);

    }

}

?>
