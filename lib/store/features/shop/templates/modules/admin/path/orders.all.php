<?php
include_once("store/features/shop/templates/modules/admin/content/OrdersList.php");

$config = TemplateConfig::Factory();
$config->contentClass = OrdersList::class;
$config->title = tr("All Orders");
$config->clearNavigation = true;
