<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class StoreSizeInputForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "size_value", "Оразмеряващ код", 1);
        $this->addInput($field);
        $field->enableTranslator(TRUE);

    }

}

?>
