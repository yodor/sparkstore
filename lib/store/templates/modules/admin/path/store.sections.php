<?php

if (URL::Current()->contains("editID")) {
    $config = Template::Editor(SectionsBean::class, SectionInputForm::class);
}
else {
    $config = Template::List(SectionsBean::class);
    $config->listFields = array("position"=>"#", "section_title"=>"Section");

    $config->summary = "Тук добавяте секции за изграждане на списъци от продукти за извеждане в началната страница.<BR>";

    $config->observer = function(TemplateEvent $event) {

        $content = $event->getSource();
        if (!($content instanceof BeanList)) throw new Exception("Incorrect event source - expected BeanList");

        if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {
            $actions = $content->getItemActions()->getActions();
            $actions->append(Action::RowSeparator());

            $actBannersGallery = TemplateContent::CreateAction("Banners Gallery", "Banner Gallery", "banners");
            $actBannersGallery->getURL()->add(new DataParameter("secID", $content->getBean()->key()));
            $actions->append($actBannersGallery);

            $actions->append(Action::RowSeparator());

            $actProducts = TemplateContent::CreateAction("See Products", "See Products", "/store/products");
            $actProducts->getURL()->add(new DataParameter("filter_section", "section_title"));
            $actProducts->getURL()->add(new URLParameter("filter", "search"));
            $actions->append($actProducts);
        }

    };

    $config->clearNavigation = true;
}