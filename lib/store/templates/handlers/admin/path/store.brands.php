<?php
$config = null;

if (URL::Current()->contains("editID")) {
    $config = Template::Editor(BrandsBean::class, BrandInputForm::class);
}
else {
    $config = Template::List(BrandsBean::class);
    $config->listFields = array("cover"=>"Cover","brand_name"=>"Brand Name", "summary"=>"Summary", "url"=>"URL", "home_visible"=>"Home Visible");
    $config->searchField = array("brand_name", "summary", "brandID");
    $config->observer = Template::WrapObserver(
        function(TemplateEvent $event) {
            if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
            $source = $event->getSource();
            if (!($source instanceof BeanList)) throw new Exception("Incorrect event source - expecting BeanList");
            $source->tableView()->getColumn("home_visible")->setCellRenderer(new BooleanCell("Yes", "No"));
            $source->tableView()->getColumn("cover")->setCellRenderer(new ImageCell());
        }, $config->observer);

    $config->clearNavigation = true;
}
Template::SetConfig($config);