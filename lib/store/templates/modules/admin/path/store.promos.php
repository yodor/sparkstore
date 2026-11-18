<?php
include_once("templates/content/BeanEditor.php");
include_once("store/beans/StorePromosBean.php");
include_once("store/forms/StorePromoInputForm.php");
include_once("templates/content/BeanList.php");

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(StorePromosBean::class, StorePromoInputForm::class);
}
else {
    $config = BeanList::Configure(StorePromosBean::class);
    $config->listFields = array("start_date"=>"Start Date", "end_date"=>"End Date",
        "target"=>"Category Target", "targetID" => "Category ID", "discount_percent"=>"Discount Percent");

    $config->clearNavigation = true;
}