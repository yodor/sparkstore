<?php
include_once("forms/InputForm.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/AttributesBean.php");
include_once("store/beans/ClassAttributesBean.php");
include_once("input/ArrayDataInput.php");
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
        $rend->na_label = '--- TOP ---';
        $rend->na_value = "0";

        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "photo", "Снимка", 0);
        $field->getProcessor()->setTransactBean(new ProductCategoryPhotosBean());
        $field->getProcessor()->setTransactBeanItemLimit(4);

        $this->addInput($field);

        $this->getInput("category_name")->enableTranslator(TRUE);
    }

}

?>
