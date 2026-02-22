<?php
include_once("components/templates/admin/GalleryViewPage.php");

include_once("beans/DynamicPagePhotosBean.php");
include_once("beans/DynamicPagesBean.php");

$rc = new BeanKeyCondition(new DynamicPagesBean(), "../list.php", array("item_title"));

$cmp = new GalleryViewPage();
$cmp->setRequestCondition($rc);

$bean = new DynamicPagePhotosBean();
$cmp->setBean($bean);

$cmp->getPage()->setName(tr("Photo Gallery") . ": " . $rc->getData("item_title"));

$cmp->render();