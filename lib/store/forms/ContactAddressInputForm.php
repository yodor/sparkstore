<?php
include_once("forms/InputForm.php");
include_once("store/beans/ContactAddressesBean.php");


class ContactAddressInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "city", "Град", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "address", "Адрес", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "map_url", "Google Maps URL", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "phone", "Телефон", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "email", "E-Mail", 0);
        $this->addInput($field);


    }

}

?>
