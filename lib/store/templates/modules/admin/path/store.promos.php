<?php
include_once("templates/content/BeanEditor.php");
include_once("templates/content/BeanList.php");
include_once("store/beans/StorePromosBean.php");
include_once("store/forms/StorePromoInputForm.php");
include_once("iterators/SelectQuery.php");

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(StorePromosBean::class, StorePromoInputForm::class);
}
else {
    $config = BeanList::Configure(StorePromosBean::class);
    $config->listFields = array("start_date" => "Start Date", "end_date" => "End Date", "target" => "Target Type",
        "target_name" => "Target", "discount_percent" => "Discount Percent", "active" => "Active");

    //the target by its name, the active promotions marked - the newest first
    $bean = new StorePromosBean();
    $query = $bean->query("sp.spID", "sp.start_date", "sp.end_date", "sp.targetID", "sp.discount_percent");
    $query->stmt->from("store_promos sp")
        ->leftJoin("product_categories pc")->on("sp.target = 'Category' AND pc.catID = sp.targetID")
        ->leftJoin("sections s")->on("sp.target = 'Section' AND s.secID = sp.targetID");
    $query->stmt->alias("COALESCE(pc.category_name, s.section_title, sp.targetID)", "target_name");
    //the texts of the values in the admin language
    $text = fn(string $value) : string => "'" . str_replace("'", "''", tr($value)) . "'";
    $query->stmt->alias("IF(sp.target = 'Section', " . $text("Section") . ", " . $text("Category") . ")", "target");
    $query->stmt->alias("IF(CURDATE() BETWEEN sp.start_date AND sp.end_date, " . $text("Yes") . ", '')", "active");
    $config->iterator = $query;

    $config->clearNavigation = true;
}
