<?php

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(BrandsBean::class, BrandInputForm::class);
}
else {
    $config = TemplateConfig::List(BrandsBean::class);
    $config->listFields = array("cover"=>"Cover","brand_name"=>"Brand Name", "summary"=>"Summary", "url"=>"URL", "home_visible"=>"Home Visible");
    $config->searchField = array("brand_name", "summary", "brandID");
    $config->observer = function(TemplateEvent $event) {
        if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
        $source = $event->getSource();

        if (!($source instanceof BeanList)) throw new Exception("Incorrect event source - expecting BeanList");
        $source->tableView()->getColumn("home_visible")->setCellRenderer(new BooleanCell("Yes", "No"));
        $source->tableView()->getColumn("cover")->setCellRenderer(new ImageCell());
    };

    $config->clearNavigation = true;
}