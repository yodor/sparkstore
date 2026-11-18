<?php

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(AttributesBean::class, AttributeInputForm::class);
}
else {
    $config = BeanList::Configure(AttributesBean::class);
    $config->listFields = array("name"=>"Name","unit"=>"Unit", "type"=>"Type");
    $config->clearNavigation = true;
}