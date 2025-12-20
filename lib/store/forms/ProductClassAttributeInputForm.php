<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("store/beans/AttributesBean.php");
include_once("store/beans/ProductClassAttributesBean.php");

include_once("input/ArrayDataInput.php");
include_once("input/validators/SimpleTextValidator.php");
include_once("components/TextComponent.php");

class ProductClassAttributeInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::SELECT, "attrID", "Достъпни входни етикети", 1);
        $this->addInput($field);

//
//        $rend = $field->getRenderer();
//        if ($rend instanceof SelectField) {
//
//        }

//        $field1 = new ArrayDataInput("attrID", "Attributes", 0);
//
//
//        //try merge even if posted count is different
//        $field1->getProcessor()->merge_with_target_loaded = true;
//
//        $field1->getProcessor()->setTransactBean(new ProductClassAttributesBean());
//        //$field1->getValueTransactor()->process_datasource_foreign_keys = true;
//        //$field1->getProcessor()->bean_copy_fields = array("class_name");
//
//        $attribs = new AttributesBean();
//        $rend = new SelectField($field1);
//
//        $rend->setIterator($attribs->query($attribs->key(), "name"));
//        $rend->getItemRenderer()->setValueKey("attrID");
//        $rend->getItemRenderer()->setLabelKey("name");
//
//        $field1->setValidator(new EmptyValueValidator());
//
//        $arend = new ArrayField($rend);
//
//        $act = new Action("inline-new", "../attributes/add.php");
//        $act->setContents("New attribute");
//        $arend->controls()->items()->append($act);
//
//        $this->addInput($field1);
//
//
//        $info = new TextComponent("Ако промените атрибутите ще се ИЗТРИЯТ от всички продукти от този клас");
//        $info->setStyle("color","red");
//
//        $arend->getAddonContainer()->items()->append($info);
    }

    public function setProductClassID(int $pclsID) : void
    {
        $input = $this->getInput("attrID");

        $select = new SQLSelect();
        $select->fields()->set("attrID", "name");
        $select->from = " attributes ";
        $select->where()->addExpression("attrID not in (SELECT attrID FROM product_class_attributes WHERE pclsID = $pclsID)");

        $rend = $input->getRenderer();

        if ($rend instanceof SelectField) {

            $rend->setIterator(new SQLQuery($select, "attrID", "attributes"));

            $rend->getItemRenderer()->setValueKey("attrID");
            $rend->getItemRenderer()->setLabelKey("name");
        }

    }
}

?>
