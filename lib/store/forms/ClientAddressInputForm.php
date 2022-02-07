<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("input/validators/SimplePhoneValidator.php");

class ClientAddressInputForm extends InputForm
{

    protected $fast_order = false;

    public function __construct(bool $fast_order=false)
    {
        parent::__construct();
        $this->fast_order = $fast_order;

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "fullname", "Име", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "phone", "Телефон", 1);
        $field->setValidator(new SimplePhoneValidator());
        $this->addInput($field);

        if (!$this->fast_order) {
            $field = DataInputFactory::Create(DataInputFactory::TEXT, "city", "Град", 1);
            $this->addInput($field);

            $field = DataInputFactory::Create(DataInputFactory::TEXT, "postcode", "Пощенски код", 1);
            $this->addInput($field);

            $field = DataInputFactory::Create(DataInputFactory::TEXT, "address1", "Адрес (ред 1)", 1);
            $this->addInput($field);

            $field = DataInputFactory::Create(DataInputFactory::TEXT, "address2", "Адрес (ред 2)", 0);
            $this->addInput($field);
        }
        else {

            $field = DataInputFactory::Create(DataInputFactory::CHECKBOX, "accept_terms", "Премам общите условия на сайта" , 1);
            $this->addInput($field);
        }


    }

//    public function renderPlain()
//    {
//        echo "<div class='ClientAddressList'>";
//
//        foreach ($this->getInputs() as $index => $field) {
//            echo "<div class='address_item'>";
//            echo "<label>" . tr($field->getLabel()) . ": </label>";
//            echo "<span>" . strip_tags(stripslashes($field->getValue())) . "</span>";
//            echo "</div>";
//        }
//
//        echo "</div>";
//    }
}

?>
