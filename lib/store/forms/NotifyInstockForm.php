<?php
include_once("forms/InputForm.php");

class NotifyInstockForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::EMAIL, "email", "Your email<BR>(we will let you know when the product is in stock)", 1);
        $this->addInput($field);

    }


}