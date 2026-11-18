<?php
include_once("store/templates/modules/admin/content/OrdersList.php");

$config = TemplateConfig::Factory();
$config->contentClass = OrdersList::class;
$config->title = tr("All Orders");
$config->clearNavigation = true;
