<?php
include_once("components/templates/admin/BeanEditorPage.php");
include_once("store/forms/ProductCategoryInputForm.php");
include_once("store/beans/ProductCategoriesBean.php");

$cmp = new BeanEditorPage();

$cmp->setBean(new ProductCategoriesBean());
$cmp->setForm(new ProductCategoryInputForm());

$cmp->render();