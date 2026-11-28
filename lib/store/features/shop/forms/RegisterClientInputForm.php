<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("input/validators/AnyValueValidator.php");
include_once("utils/url/DynamicPageURL.php");

class RegisterClientInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "fullname", "Name", 1);

        $label = new Component();
        $label->setContents(tr("Your full name"));

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::EMAIL, "email", "Email", 1);

        $label = new Component();
        $label->setContents(tr("You will receive a confirmation email"));

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Phone", 1);

        $label = new Component();
        $label->setContents(tr("For contact on delivery"));

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        $this->addInput($field);



        $field = DataInputFactory::Create(InputType::PASSWORD, "password", "Password", 1);
        $field->setValidator(new AnyValueValidator());
        $field->getRenderer()->setAttribute("autocomplete","off");
        $this->addInput($field);

        $label = new Component();
        $label->setContents(tr("At least 6 characters"));

//        $field->getRenderer()->setAddonRenderMode(InputField::ADDON_MODE_OUSIDE);
        $field->getRenderer()->getAddonContainer()->items()->append($label);

        //the client sees what gets stored - checked here and before submit (RegisterForm.js)
        $field = DataInputFactory::Create(InputType::PASSWORD, "password_repeat", "Repeat the password", 1);
        $field->setValidator(new AnyValueValidator());
        $field->getRenderer()->setAttribute("autocomplete","off");
        $field->getProcessor()->skip_transaction = TRUE;
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::HIDDEN, "challenge", "challenge", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::HIDDEN, "token", "token", 0);
        $this->addInput($field);

        $termsURL = DynamicPageURL::Group("terms");
        $field = DataInputFactory::Create(InputType::CHECKBOX, "accept_terms", tr("I accept the <a href='%s'>Terms and Conditions</a>", $termsURL->fullURL()), 1);
        $this->addInput($field);


    }

    /**
     * The password and its repeat must match (both empty in the profile: the password is kept)
     * @return void
     * @throws Exception
     */
    public function validate() : void
    {
        parent::validate();

        $password = (string)$this->getInput("password")->getValue();
        $repeat = (string)$this->getInput("password_repeat")->getValue();
        if (strcmp($password, $repeat) !== 0) {
            $this->getInput("password_repeat")->setError(tr("The passwords do not match"));
            throw new Exception(tr("The passwords do not match"));
        }
    }

}