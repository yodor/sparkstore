<?php
include_once("forms/InputForm.php");

class FastOrderProductForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "fullname", "Име", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "phone", "Телефон", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "address", "Адрес за доставка (или офис на куриер)", 1);
        $this->addInput($field);

    }

}
?>