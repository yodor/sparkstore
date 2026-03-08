<?php

if (URL::Current()->contains("editID")) {

}
else {
    $config = TemplateConfig::Factory();

    $config->contentClass = ProductsList::class;

    $config->clearNavigation = true;
}