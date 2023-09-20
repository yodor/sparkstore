<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");
include_once("store/forms/ProductClassInputForm.php");
include_once("store/beans/ProductClassesBean.php");

$cmp = new BeanEditorPage();
$cmp->setBean(new ProductClassesBean());
$cmp->setForm(new ProductClassInputForm());
$cmp->render();

?>
