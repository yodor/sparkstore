<?php
include_once("store/templates/modules/admin/content/CourierOfficesAdmin.php");

$config = TemplateConfig::Factory();
$config->contentClass = CourierOfficesAdmin::class;
$config->title = tr("Courier Offices");
$config->clearNavigation = true;
