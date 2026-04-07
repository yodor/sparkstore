<?php
include_once("store/beans/ProductCategoriesBean.php");
Template::Condition( new BeanKeyCondition(new ProductCategoriesBean(), Module::PathURL("/store/categories"), array("category_name")) );

if (URL::Current()->contains("editID")) {

    $config = TemplateConfig::Editor(ProductCategoryBannersBean::class, PhotoForm::class);
    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) {
        if (!$event->isEvent(TemplateEvent::CONTENT_SETUP)) return;

        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect event source - expected BeanEditor");

        $content->config()->title = tr("Banner").": " . Template::Condition()->getData("category_name");
    };
}
else {
    $config = TemplateConfig::Gallery(ProductCategoryBannersBean::class);
    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) {
        if (!$event->isEvent(TemplateEvent::CONTENT_SETUP)) return;

        $content = $event->getSource();
        if (!($content instanceof BeanGallery)) throw new Exception("Incorrect event source - expected BeanGallery");

        $content->config()->title = tr("Banners Gallery") . ": " . Template::Condition()->getData("category_name");

    };

}