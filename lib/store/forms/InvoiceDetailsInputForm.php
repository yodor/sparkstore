<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class InvoiceDetailsInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "company_name", "Company name", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "acc_person", "Accountable person", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "city", "City", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "postcode", "Postcode", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "address1", "Address (line 1)", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "address2", "Address (line 2)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "vat", "Registration number (UIC)", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::CHECKBOX, "vat_registered", "VAT registered", 0);
        $this->addInput($field);
    }

}