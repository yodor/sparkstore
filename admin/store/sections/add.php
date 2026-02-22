<?php
include_once("components/templates/admin/BeanEditorPage.php");
include_once("store/forms/SectionInputForm.php");
include_once("store/beans/SectionsBean.php");

$cmp = new BeanEditorPage();
$cmp->setBean(new SectionsBean());
$cmp->setForm(new SectionInputForm());
$cmp->render();