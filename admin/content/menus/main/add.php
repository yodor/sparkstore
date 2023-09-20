<?php
include_once("session.php");
include_once ("templates/admin/BeanEditorPage.php");
include_once("forms/MenuItemForm.php");
include_once("beans/MenuItemsBean.php");

$bean = new MenuItemsBean();

$cmp = new BeanEditorPage();
$cmp->setBean($bean);
$cmp->setForm(new MenuItemForm($bean));

$cmp->render();

?>
