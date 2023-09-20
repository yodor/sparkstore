<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");
include_once("forms/NewsItemInputForm.php");
include_once("beans/NewsItemsBean.php");

$cmp = new BeanEditorPage();
$cmp->setBean(new NewsItemsBean());
$cmp->setForm(new NewsItemInputForm());
$cmp->render();

?>