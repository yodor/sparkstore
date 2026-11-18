<?php
include_once("store/beans/GalleryPhotosBean.php");
include_once("forms/PhotoForm.php");

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(GalleryPhotosBean::class, PhotoForm::class);
    $config->title = tr("Gallery Photo");
}
else {
    $config = BeanGallery::Configure(GalleryPhotosBean::class);
    $config->title = tr("Gallery Photos");
    $config->clearNavigation = true;
}
