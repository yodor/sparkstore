<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");
include_once("forms/DynamicPageForm.php");
include_once("beans/DynamicPagesBean.php");

$cmp = new BeanEditorPage();
$cmp->setBean(new DynamicPagesBean());
$cmp->setForm(new DynamicPageForm());
$cmp->render();

?>