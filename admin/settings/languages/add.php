<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");
include_once("beans/LanguagesBean.php");
include_once("forms/LanguageForm.php");

$cmp = new BeanEditorPage();

$cmp->setBean(new LanguagesBean());
$cmp->setForm(new LanguageForm());

$cmp->render();


?>