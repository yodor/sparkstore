<?php

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(StorePromosBean::class, StorePromoInputForm::class);
}
else {
    $config = BeanList::Configure(StorePromosBean::class);
    $config->listFields = array("start_date"=>"Start Date", "end_date"=>"End Date",
        "target"=>"Category Target", "targetID" => "Category ID", "discount_percent"=>"Discount Percent");

    $config->clearNavigation = true;
}