<?php

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(StorePromosBean::class, StorePromoInputForm::class);
}
else {
    $config = TemplateConfig::List(StorePromosBean::class);
    $config->listFields = array("start_date"=>"Start Date", "end_date"=>"End Date",
        "target"=>"Category Target", "targetID" => "Category ID", "discount_percent"=>"Discount Percent");

    $config->clearNavigation = true;
}