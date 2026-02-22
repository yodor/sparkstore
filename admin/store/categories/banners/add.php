<?php
include_once("components/templates/admin/BeanEditorPage.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductCategoryBannersBean.php");

include_once("forms/PhotoForm.php");

$rc = new BeanKeyCondition(new ProductCategoriesBean(), "../list.php");


$cmp = new BeanEditorPage();
$cmp->setRequestCondition($rc);

$cmp->getPage()->setName(tr("Banners Gallery") . ": " . $rc->getData("category_name"));


$photos = new ProductCategoryBannersBean();

$form = new PhotoForm();
$field = DataInputFactory::Create(InputType::TEXT, "link", "Link", 0);
$form->addInput($field);

$cmp->setBean($photos);
$cmp->setForm($form);

$cmp->render();