<?php
include_once("store/features/shop/templates/modules/admin/content/CourierOfficesAdmin.php");

$config = TemplateConfig::Factory();
$config->contentClass = CourierOfficesAdmin::class;
$config->title = tr("Courier Offices");
$config->clearNavigation = true;
