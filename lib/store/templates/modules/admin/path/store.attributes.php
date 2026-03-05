<?php

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(AttributesBean::class, AttributeInputForm::class);
}
else {
    $config = TemplateConfig::List(AttributesBean::class);
    $config->listFields = array("name"=>"Name","unit"=>"Unit", "type"=>"Type");
    $config->clearNavigation = true;
}