<?php
include_once("forms/InputForm.php");
include_once("store/beans/SectionsBean.php");

class SectionChooserForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::SELECT_MULTI, "secID", "Section", 0);
        $proc = new InputProcessor($field);
        $renderer = new SelectMultipleField($field);

        //$field = DataInputFactory::Create(DataInputFactory::SELECT_MULTI, "secID", "Section", 0);
        $rend = $field->getRenderer();
        $sb = new SectionsBean();
        $rend->setIterator($sb->query($sb->key(),"section_title"));
        $rend->getItemRenderer()->setValueKey($sb->key());
        $rend->getItemRenderer()->setLabelKey("section_title");

//        $product_sections = new ProductSectionsBean();
//        $field->getProcessor()->setTransactBean($product_sections);
        $this->addInput($field);


    }


}