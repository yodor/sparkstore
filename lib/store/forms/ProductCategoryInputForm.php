<?php
include_once("forms/InputForm.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductCategoryPhotosBean.php");

class ProductCategoryInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = new DataInput("category_name", "Име на категория", 1);
        new TextField($field);
        $this->addInput($field);

        $field = new DataInput("parentID", "Родителска категория", 1);

        $pcats = new ProductCategoriesBean();

        $rend = new NestedSelectField($field);

        $rend->setIterator(new SQLQuery($pcats->selectTree(array("category_name")), $pcats->key(), $pcats->getTableName()));
        $rend->getItemRenderer()->setValueKey($pcats->key());
        $rend->getItemRenderer()->setLabelKey("category_name");
        $rend->setDefaultOption("--- TOP ---", "0");

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::MCE_TEXTAREA, "category_description", "Описание (до 2000 символа)", 0);
        $this->addInput($field);

        $field = new DataInput("category_seotitle", "SEO Заглавие (опция)", 0);
        new TextField($field);
        $this->addInput($field);

        $field = new DataInput("category_seodescription", "SEO Описание (опция - 150 символа)", 0);
        $rend = new TextArea($field);
        $rend->input()?->setAttribute("maxLength", 150);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::SESSION_IMAGE, "photo", "Снимка", 0);
        $field->getProcessor()->setTransactBean(new ProductCategoryPhotosBean());
        $field->getProcessor()->setTransactBeanItemLimit(1);

        $this->addInput($field);

        $this->getInput("category_name")->enableTranslator(TRUE);
    }

}

?>
