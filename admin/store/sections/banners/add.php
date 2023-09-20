<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");
include_once("store/beans/SectionsBean.php");
include_once("store/beans/SectionBannersBean.php");

include_once("forms/PhotoForm.php");

$rc = new BeanKeyCondition(new SectionsBean(), "../list.php");


$cmp = new BeanEditorPage();
$cmp->setRequestCondition($rc);

$cmp->getPage()->setName(tr("Banners Gallery") . ": " . $rc->getData("section_title"));


$photos = new SectionBannersBean();

$form = new PhotoForm();
$field = new DataInput("link", "Link", 0);
new TextField($field);
$form->addInput($field);

$cmp->setBean($photos);
$cmp->setForm($form);

$cmp->render();
?>
