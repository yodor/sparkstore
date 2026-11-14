<?php
include_once("store/templates/content/LogoEditor.php");

$config = TemplateConfig::Factory();
$config->contentClass = LogoEditor::class;
$config->title = tr("Logo Management");
$config->clearNavigation = true;
