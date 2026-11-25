<?php
include_once("forms/InputForm.php");
include_once("forms/SEOInputGroup.php");

class SectionInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "section_title", "Section", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::CHECKBOX, "home_visible", "Show on the home page of the site (option)", 0);
        $this->addInput($field);

        SEOInputGroup::AddTo($this, false);

    }

}