<?php
include_once("session.php");
include_once("components/templates/admin/GalleryViewPage.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductCategoryBannersBean.php");


$rc = new BeanKeyCondition(new ProductCategoriesBean(), "../list.php", array("category_name"));


$cmp = new GalleryViewPage();
$cmp->setRequestCondition($rc);

$cmp->getPage()->setName(tr("Banners Gallery") . ": " . $rc->getData("category_name"));

$cmp->setBean(new ProductCategoryBannersBean());

$cmp->render();