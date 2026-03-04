<?php

if (URL::Current()->contains("editID")) {
    $config = Template::Editor(AttributesBean::class, AttributeInputForm::class);
}
else {
    $config = Template::List(AttributesBean::class);
    $config->listFields = array("name"=>"Name","unit"=>"Unit", "type"=>"Type");

    $config->summary = "Тук може да добавяте входни етикет за ползване в продуктовите класове";

    $config->clearNavigation = true;
}