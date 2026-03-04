<?php

$config = null;

if (URL::Current()->contains("editID")) {

}
else {
    $config = new TemplateConfig();

    $config->contentClass = ProductsList::class;

    $config->clearNavigation = true;
}

Template::Config($config);