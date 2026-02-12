<?php
include_once("forms/InputForm.php");

class CourierOfficeInputForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXTAREA, "office", "Въведете адрес на офис за доставка", 1);
        //$field->getRenderer()->input()?->setAttribute("readonly", "1");
        $this->addInput($field);

    }

//    public function renderPlain()
//    {
//        echo "<div class='InvoiceDetailsList'>";
//
//        foreach ($this->getInputs() as $index => $field) {
//            echo "<div class='address_item'>";
//            echo "<label>" . tr($field->getLabel()) . ": </label>";
//            $value = strip_tags(stripslashes($field->getValue()));
//            $value = str_replace("\r\n", "<BR>", $value);
//            echo "<span>" . $value . "</span>";
//            echo "</div>";
//        }
//
//        echo "</div>";
//    }

}