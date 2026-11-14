<?php
include_once("store/templates/content/OrdersList.php");

$config = TemplateConfig::Factory();
$config->contentClass = OrdersList::class;
$config->title = tr("All Orders");
$config->clearNavigation = true;
