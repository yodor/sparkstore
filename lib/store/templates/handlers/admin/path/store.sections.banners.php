<?php
include_once("store/beans/SectionsBean.php");
Template::Condition( new BeanKeyCondition(new SectionsBean(), Template::PathURL("/store/sections/"), array("section_title")) );


$config = null;
if (URL::Current()->contains("editID")) {
    $config = Template::Editor(SectionBannersBean::class, PhotoForm::class);
    $config->observer = Template::WrapObserver(
        function(TemplateEvent $event) use($config) {
            if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
            $field = DataInputFactory::Create(InputType::TEXT, "link", "Link", 0);
            $event->getSource()->editor()->getForm()->addInput($field);

        }, $config->observer);
}
else {
    $config = Template::Gallery(SectionBannersBean::class);
}

//
$config->observer = Template::WrapObserver(
    function(TemplateEvent $event) use($config) {

//        $cmp->getPage()->setName(tr("Banners Gallery") . ": " . $rc->getData("section_title"));

        if (!$event->isEvent(TemplateEvent::CONTENT_SETUP)) return;
        $config->title .= " - " . Template::Condition()->getData("section_title");

    }, $config->observer);

Template::Config($config);