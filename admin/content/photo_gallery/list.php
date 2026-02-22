<?php
include_once("components/templates/admin/GalleryViewPage.php");

include_once("store/beans/GalleryPhotosBean.php");

$cmp = new GalleryViewPage();

$cmp->setBean(new GalleryPhotosBean());

$cmp->getPage()->navigation()->clear();


$cmp->render();