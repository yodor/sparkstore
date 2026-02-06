<?php
include_once("forms/InputForm.php");

class SectionInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "section_title", "Секция", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "section_seodescription", "СЕО Описание", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::CHECKBOX, "home_visible", "Показвай в 'Начало' на сайта (опция)", 0);
        $this->addInput($field);

    }

}

?>
