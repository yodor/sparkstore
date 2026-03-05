<?php
include_once("store/beans/ProductCategoriesBean.php");
Template::Condition( new BeanKeyCondition(new ProductCategoriesBean(), Template::PathURL("/store/categories"), array("category_name")) );

$config = null;
if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(ProductCategoryBannersBean::class, PhotoForm::class);
    $config->observer = TemplateConfig::WrapObserver(
        function(TemplateEvent $event) use($config) {
            if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
            $field = DataInputFactory::Create(InputType::TEXT, "link", "Link", 0);
            $event->getSource()->editor()->getForm()->addInput($field);

        }, $config->observer);
}
else {
    $config = TemplateConfig::Gallery(ProductCategoryBannersBean::class);
}

//
$config->observer = TemplateConfig::WrapObserver(
    function(TemplateEvent $event) use($config) {

        if (!$event->isEvent(TemplateEvent::CONTENT_SETUP)) return;
        $config->title .= " - " . Template::Condition()->getData("category_name");

    }, $config->observer);