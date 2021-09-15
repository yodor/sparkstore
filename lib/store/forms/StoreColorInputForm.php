<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class StoreColorInputForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "color", "Име на цвят", 1);
        $this->addInput($field);
        $field->enableTranslator(TRUE);

        $field = DataInputFactory::Create(DataInputFactory::COLOR_CODE, "color_code", "Цветови код", 0);

        $this->addInput($field);

    }

}

?>
