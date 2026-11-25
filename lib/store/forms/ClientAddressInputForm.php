<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("input/validators/SimplePhoneValidator.php");


class ClientAddressInputForm extends InputForm
{

    /**
     * @var bool If set allow ordering of items inside cart without registration
     */
    protected $fast_order = false;

    public function __construct(bool $fast_order=false)
    {
        parent::__construct();
        $this->fast_order = $fast_order;

        $field = DataInputFactory::Create(InputType::TEXT, "fullname", "Name", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Phone", 1);
        $field->setValidator(new SimplePhoneValidator());
        $this->addInput($field);

        if (!$this->fast_order) {
            $field = DataInputFactory::Create(InputType::TEXT, "city", "City", 1);
            $this->addInput($field);

            $field = DataInputFactory::Create(InputType::TEXT, "postcode", "Postcode", 1);
            $this->addInput($field);

            $field = DataInputFactory::Create(InputType::TEXT, "address1", "Address (line 1)", 1);
            $this->addInput($field);

            $field = DataInputFactory::Create(InputType::TEXT, "address2", "Address (line 2)", 0);
            $this->addInput($field);
        }
        else {

            $field = DataInputFactory::Create(InputType::CHECKBOX, "accept_terms", "I accept the terms and conditions of the site" , 1);
            $this->addInput($field);
        }


    }

    public function isFastOrder() : bool
    {
        return $this->fast_order;
    }
}