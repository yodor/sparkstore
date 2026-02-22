<?php
include_once("components/templates/admin/BeanEditorPage.php");
include_once("beans/DynamicPagesBean.php");
include_once("beans/DynamicPagePhotosBean.php");

include_once("forms/PhotoForm.php");

$rc = new BeanKeyCondition(new DynamicPagesBean(), "list.php");

$cmp = new BeanEditorPage();
$cmp->setRequestCondition($rc);

$photos = new DynamicPagePhotosBean();
$cmp->setBean($photos);
$cmp->setForm(new PhotoForm());

$cmp->render();