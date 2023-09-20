<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");

include_once("forms/FAQItemInputForm.php");
include_once("beans/FAQItemsBean.php");

$cmp = new BeanEditorPage();
$cmp->setBean(new FAQItemsBean());
$cmp->setForm(new FAQItemInputForm());
$cmp->render();

?>