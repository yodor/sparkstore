<?php
include_once("store/beans/ProductCategoriesBean.php");
Template::Condition( new BeanKeyCondition(new ProductCategoriesBean(), Module::PathURL("/store/categories"), array("category_name")) );

$config = null;
if (URL::Current()->contains("editID")) {

    $config = TemplateConfig::Editor(ProductCategoryBannersBean::class, PhotoForm::class);
    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) use($config) {

        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect event source - expected BeanEditor");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $config->title = tr("Banner").": " . Template::Condition()->getData("category_name");
        }
        else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {
            $field = DataInputFactory::Create(InputType::TEXT, "link", "Link", 0);
            $event->getSource()->editor()->getForm()->addInput($field);
        }

    };
}
else {
    $config = TemplateConfig::Gallery(ProductCategoryBannersBean::class);
    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) use($config) {

        $content = $event->getSource();
        if (!($content instanceof BeanGallery)) throw new Exception("Incorrect event source - expected BeanGallery");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $config->title = tr("Banners Gallery") . ": " . Template::Condition()->getData("category_name");
        }

    };

}