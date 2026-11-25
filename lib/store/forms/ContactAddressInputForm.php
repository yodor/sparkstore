<?php
include_once("forms/InputForm.php");
include_once("store/beans/ContactAddressesBean.php");


class ContactAddressInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "city", "City", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "address", "Address", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "map_url", "Google Maps URL", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Phone", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "email", "E-Mail", 0);
        $this->addInput($field);


    }

}