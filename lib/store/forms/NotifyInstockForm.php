<?php
include_once("forms/InputForm.php");

class NotifyInstockForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::EMAIL, "email", "Вашият E-mail<BR>(Ще ви известим при наличие на продукта)", 1);
        $this->addInput($field);

    }


}
?>