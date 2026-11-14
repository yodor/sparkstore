<?php
include_once("store/beans/GalleryPhotosBean.php");
include_once("forms/PhotoForm.php");

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(GalleryPhotosBean::class, PhotoForm::class);
    $config->title = tr("Gallery Photo");
}
else {
    $config = TemplateConfig::Gallery(GalleryPhotosBean::class);
    $config->title = tr("Gallery Photos");
    $config->clearNavigation = true;
}
