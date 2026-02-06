<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("iterators/DBEnumIterator.php");

class FAQItemInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(InputType::SELECT, "section", "Секция", 1);

        $data = new DBEnumIterator("faq_items", "section");
        $rend = $field->getRenderer();
        $rend->setIterator($data);
        $rend->getItemRenderer()->setValueKey(ArrayDataIterator::KEY_VALUE);
        $rend->getItemRenderer()->setLabelKey(ArrayDataIterator::KEY_VALUE);

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "question", "Въпрос", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "answer", "Отговор", 1);
        $this->addInput($field);

    }

}

?>
