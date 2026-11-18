<?php
include_once("store/templates/modules/admin/content/LogoEditor.php");

$config = TemplateConfig::Factory();
$config->contentClass = LogoEditor::class;
$config->title = tr("Logo Management");
$config->clearNavigation = true;
