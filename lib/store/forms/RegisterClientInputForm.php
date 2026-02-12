<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("input/validators/AnyValueValidator.php");

class RegisterClientInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "fullname", "Име", 1);

        $label = new Component();
        $label->setContents(tr("Вашето пълно име"));

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::EMAIL, "email", "Email", 1);

        $label = new Component();
        $label->setContents(tr("Ще Ви изпратим e-mail за потвърждение"));

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Телефон", 1);

        $label = new Component();
        $label->setContents(tr("За контакт при доставка"));

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);



        $field = DataInputFactory::Create(InputType::PASSWORD, "password", "Парола", 1);
        $field->setValidator(new AnyValueValidator());
        $field->getRenderer()->setAttribute("autocomplete","off");
        $this->addInput($field);

        $label = new Component();
        $label->setContents(tr("Необходими са поне 6 символа"));

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $field = DataInputFactory::Create(InputType::HIDDEN, "pass", "Парола", 0);
        $this->addInput($field);

        $termsURL = new URL(Spark::Get(Config::LOCAL)."/pages/index.php?page_class=terms");
        $field = DataInputFactory::Create(InputType::CHECKBOX, "accept_terms", "Прочетох и приемам <a href='{$termsURL->fullURL()}'>Общите условия</a>", 1);
        $this->addInput($field);

    }

}