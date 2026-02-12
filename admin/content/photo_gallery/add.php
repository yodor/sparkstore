<?php
include_once("session.php");
include_once("components/templates/admin/BeanEditorPage.php");
include_once("store/beans/GalleryPhotosBean.php");

include_once("forms/PhotoForm.php");

$cmp = new BeanEditorPage();

$photos = new GalleryPhotosBean();

$cmp->setBean($photos);
$cmp->setForm(new PhotoForm());

$cmp->render();