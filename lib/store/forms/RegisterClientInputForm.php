<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("input/validators/AnyValueValidator.php");

class RegisterClientInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "fullname", "Име", 1);

        $label = new Component();
        $label->translation_enabled = true;
        $label->setContents("Вашето пълно име");

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::EMAIL, "email", "Email", 1);

        $label = new Component();
        $label->translation_enabled = true;
        $label->setContents("Ще Ви изпратим e-mail за потвърждение");

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "phone", "Телефон", 1);

        $label = new Component();
        $label->translation_enabled = true;
        $label->setContents("За контакт при доставка");

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);



        $field = DataInputFactory::Create(DataInputFactory::PASSWORD, "password", "Парола", 1);
        $field->setValidator(new AnyValueValidator());
        $field->getRenderer()->setAttribute("autocomplete","off");
        $this->addInput($field);

        $label = new Component();
        $label->translation_enabled = true;
        $label->setContents("Необходими са поне 6 символа");

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $field = DataInputFactory::Create(DataInputFactory::HIDDEN, "pass", "Парола", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::CHECKBOX, "accept_terms", "Прочетох и приемам <a href='".LOCAL."/pages/index.php?page_class=terms"."'>Общите условия</a>", 1);
        $this->addInput($field);


    }

}

?>
