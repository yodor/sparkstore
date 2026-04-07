<?php

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(ProductsBean::class, ProductInputForm::class);
}
else {
    $config = TemplateConfig::Factory();

    $config->contentClass = ProductsList::class;

    $config->clearNavigation = true;
}