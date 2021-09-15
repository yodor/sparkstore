<?php
include_once("forms/InputForm.php");

class SectionInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "section_title", "Секция", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::CHECKBOX, "home_visible", "Показвай в 'Начало' на сайта", 0);
        $this->addInput($field);

    }

}

?>
