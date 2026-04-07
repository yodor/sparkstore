<?php
include_once("store/beans/SectionsBean.php");
Template::Condition( new BeanKeyCondition(new SectionsBean(), Module::PathURL("/store/sections"), array("section_title")) );

if (URL::Current()->contains("editID")) {

    $config = TemplateConfig::Editor(SectionBannersBean::class, PhotoForm::class);

    //Signal TemplateContent to use Template::Condition during setup() call to add match() on the default bean->select,
    //by doing so subsequent query() calls of the bean would return filtered data for the iterators

    //Signal BeanEditor to use TemplateCondition during initialize() call to assign insert value to the transactor
    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) {

        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect event source - expected BeanEditor");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $content->config()->title = tr("Banner") .": ". Template::Condition()->getData("section_title");
        }

    };
}
else {
    $config = TemplateConfig::Gallery(SectionBannersBean::class);

    //Signal TemplateContent to use Template::Condition during setup() call to add match() on the default bean->select,
    //by doing so subsequent query() calls of the bean would return filtered data for the iterators
    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) use($config) {

        $content = $event->getSource();
        if (!($content instanceof BeanGallery)) throw new Exception("Incorrect event source - expected BeanGallery");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $config->title = tr("Banners Gallery") .": " . Template::Condition()->getData("section_title");
        }

    };
}