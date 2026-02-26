<?php

if (isset($editor)) {
    $config = Template::Editor(ProductCategoryBannersBean::class, PhotoForm::class);
    $config->observer = function(TemplateEvent $event) use($config) {
        if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {
            $field = DataInputFactory::Create(InputType::TEXT, "link", "Link", 0);
            $event->getSource()->editor()->getForm()->addInput($field);
        }
        if ($event->isEvent(TemplateEvent::CONTENT_CREATED)) {
            $config->title.= " - " . $config->condition->getData("category_name");
        }
    };
}
else {
    $config = Template::Gallery(ProductCategoryBannersBean::class);
    $config->observer = function(TemplateEvent $event) use ($config) {
        if ($event->isEvent(TemplateEvent::CONTENT_CREATED)) {
            $config->title.= " - " . $config->condition->getData("category_name");
        }
    };
}

include_once("store/beans/ProductCategoriesBean.php");
if ($config) $config->condition = new BeanKeyCondition(new ProductCategoriesBean(), "../list.php", array("category_name"));