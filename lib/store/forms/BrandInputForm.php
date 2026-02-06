<?php
include_once("forms/InputForm.php");
include_once("input/validators/URLValidator.php");

class BrandInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "brand_name", "Име на марка", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "url", "URL", 0);
        $field->setValidator(new URLValidator());
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::MCE_TEXTAREA, "summary", "Описание", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::SESSION_IMAGE, "photo", "Снимка", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::CHECKBOX, "home_visible", "Показвай в 'Начало' на сайта", 0);
        $this->addInput($field);

    }

}

?>