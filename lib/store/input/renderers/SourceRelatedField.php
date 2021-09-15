<?php
include_once("input/renderers/DataIteratorField.php");
include_once("components/renderers/items/DataIteratorItem.php");
include_once("store/beans/ClassAttributesBean.php");

class SourceAttributeItem extends DataIteratorItem
{

    //TODO: add methods to set 'caID' string and attribute_unit
    public function renderImpl()
    {
        echo "<label class='SourceAttributeName' data='attribute_name'>" . $this->label . "</label>";

        echo "<input class='SourceAttributeValue' data='attribute_value' type='text' value='{$this->value}' name='{$this->name}'>";

        echo "<input data='foreign_key' type='hidden' name='fk_{$this->name}' value='caID:{$this->id}'>";

        echo "<label class='SourceAttributeUnit' data='attribute_unit'>" . $this->data["attribute_unit"] . "</label>";
    }

}

class IteratorRelatedField extends DataIteratorField
{

    public function __construct(DataInput $input)
    {
        parent::__construct($input);
        $this->setItemRenderer(new SourceAttributeItem());

    }

    public function setIterator(IDataIterator $query)
    {
        parent::setIterator($query);
        $this->addClassName(get_class($query));
    }

    public function requiredStyle() : array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/SourceRelatedField.css";
        return $arr;
    }

    protected function renderItems()
    {

        if ($this->iterator->count() < 1) {
            echo tr("No optional attributes");
            return;
        }

        //$this->list_key = $this->input->getName();

        //$this->list_key = "caID";

        parent::renderItems();
    }
}

?>
