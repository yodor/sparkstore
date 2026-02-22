<?php
include_once("components/templates/admin/BeanListPage.php");

$cmp = new BeanListPage();

$menu = array(new MenuItem("Translator", "translator/list.php", "translator"));
$cmp->getPage()->setPageMenu($menu);

$cmp->setBean(new LanguagesBean());
$cmp->setListFields(array("lang_code"=>"Language Code", "language" => "Language"));

$cmp->getPage()->navigation()->clear();

$cmp->render();