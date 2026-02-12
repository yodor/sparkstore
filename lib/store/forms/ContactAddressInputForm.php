<?php
include_once("forms/InputForm.php");
include_once("store/beans/ContactAddressesBean.php");


class ContactAddressInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "city", "Град", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "address", "Адрес", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "map_url", "Google Maps URL", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Телефон", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "email", "E-Mail", 0);
        $this->addInput($field);


    }

}