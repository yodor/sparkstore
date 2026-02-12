<?php
include_once("session.php");
include_once("components/templates/admin/ConfigEditorPage.php");
include_once("forms/SEOConfigForm.php");

$cmp = new ConfigEditorPage();
$cmp->setConfigSection("seo");

$form = new SEOConfigForm();
$cmp->setForm($form);

$cmp->getPage()->navigation()->clear();

$cmp->render();