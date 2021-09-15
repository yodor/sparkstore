<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("store/beans/AttributesBean.php");
include_once("store/beans/ClassAttributesBean.php");
include_once("input/ArrayDataInput.php");

class ProductClassInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "class_name", "Class Name", 1);
        $this->addInput($field);

        $field->enableTranslator(FALSE);

        $field1 = new ArrayDataInput("attribute_name", "Attributes", 0);

        $field1->getProcessor()->setTransactBean(new ClassAttributesBean());
        // 	  $field1->getValueTransactor()->process_datasource_foreign_keys = true;
        $field1->getProcessor()->bean_copy_fields = array("class_name");

        $attribs = new AttributesBean();
        $rend = new SelectField($field1);

        $rend->setIterator($attribs->queryFull());
        $rend->getItemRenderer()->setValueKey("name");
        $rend->getItemRenderer()->setLabelKey("name");

        $field1->setValidator(new EmptyValueValidator());

        $arend = new ArrayField($rend);

        $act = new Action("inline-new", "../attributes/add.php");
        $act->setContents("New attribute");
        $arend->addControl($act);

        $this->addInput($field1);

    }

}

?>
