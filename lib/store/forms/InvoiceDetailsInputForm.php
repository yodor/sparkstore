<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class InvoiceDetailsInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "company_name", "Име на фирма", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "acc_person", "МОЛ", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "city", "Град", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "postcode", "Пощенски код", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "address1", "Адрес (ред 1)", 1);
        $this->addInput($field);
        $field = DataInputFactory::Create(InputType::TEXT, "address2", "Адрес (ред 2)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "vat", "Рег. номер (ЕИК)", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::CHECKBOX, "vat_registered", "Регистрация по ДДС", 0);
        $this->addInput($field);
    }

//    public function renderPlain()
//    {
//        echo "<div class='InvoiceDetailsList'>";
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
