<?php
include_once("store/beans/ProductsBean.php");

Template::Condition(new BeanKeyCondition(new ProductsBean(), Module::PathURL("/store/products"), array("product_name")));

if (URL::Current()->contains("editID")) {

    $config = TemplateConfig::Editor(ProductPhotosBean::class, PhotoForm::class);
    $config->useCondition = true;

    $config->observer = function(TemplateEVent $event)  {
        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect event source - expected BeanEditor");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            //properly append to the navigation history with different name than the list itself
            $content->config()->title = tr("Image").": ". Template::Condition()->getData("product_name");
        }

    };
}
else {

    $config = TemplateConfig::Gallery(ProductPhotosBean::class);
    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) {
        $content = $event->getSource();
        if (!($content instanceof BeanGallery)) throw new Exception("Incorrect event source - expected BeanGallery");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $content->config()->title = tr("Image Gallery") . ": " . Template::Condition()->getData("product_name");
        }
    };

}