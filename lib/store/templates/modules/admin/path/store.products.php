<?php

if (URL::Current()->contains("editID")) {

}
else {
    $config = new TemplateConfig();

    $config->contentClass = ProductsList::class;

    $config->clearNavigation = true;
}