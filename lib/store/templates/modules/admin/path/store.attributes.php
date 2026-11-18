<?php
include_once("templates/content/BeanEditor.php");
include_once("store/beans/AttributesBean.php");
include_once("store/forms/AttributeInputForm.php");
include_once("templates/content/BeanList.php");

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(AttributesBean::class, AttributeInputForm::class);
}
else {
    $config = BeanList::Configure(AttributesBean::class);
    $config->listFields = array("name"=>"Name","unit"=>"Unit", "type"=>"Type");
    $config->clearNavigation = true;
}