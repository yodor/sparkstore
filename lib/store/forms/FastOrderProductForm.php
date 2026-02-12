<?php
include_once("forms/InputForm.php");

class FastOrderProductForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "fullname", "Име", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Телефон", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "address", "Адрес за доставка", 1);
        $this->addInput($field);

    }

}