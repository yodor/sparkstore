<?php
include_once("forms/InputForm.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductCategoryPhotosBean.php");

class ProductCategoryInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::TEXT, "category_name", "Име на категория", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::NESTED_SELECT, "parentID", "Родителска категория", 1);

        $pcats = new ProductCategoriesBean();

        $rend = $field->getRenderer();

        $rend->setIterator(new SQLQuery($pcats->selectTree(array("category_name")), $pcats->key(), $pcats->getTableName()));
        $rend->getItemRenderer()->setValueKey($pcats->key());
        $rend->getItemRenderer()->setLabelKey("category_name");
        $rend->setDefaultOption("--- TOP ---", "0");

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::MCE_TEXTAREA, "category_description", "Описание (до 2000 символа)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "category_seotitle", "SEO Заглавие (опция)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "category_seodescription", "SEO Описание (опция - 150 символа)", 0);
        $field->getRenderer()->input()?->setAttribute("maxLength", 150);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "conversionID", "Conversion Tag", 0);
        $field->getRenderer()->input()->setAttribute("size", "50em");
        $field->getRenderer()->input()->setAttribute("placeholder", "AW-CONVERSION_ID/CONVERSION_LABEL");
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::SESSION_IMAGE, "photo", "Снимка", 0);
        $field->getProcessor()->setTransactBean(new ProductCategoryPhotosBean());
        $field->getProcessor()->setTransactBeanItemLimit(1);

        $this->addInput($field);

        $this->getInput("category_name")->enableTranslator(TRUE);
    }

}