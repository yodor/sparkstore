<?php
include_once("session.php");
include_once("store/utils/SellableItem.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/pages/AdminPageBase.php");

$page = new AdminPageBase();

header( "Content-Type: text/csv" );
header( "Content-Disposition: attachment;filename=catalog.csv");
$fp = fopen("php://output", "w");

$keys = array("id", "content_id", "title", "description", "availability", "condition", "link", "image_link", "brand", "product_type", "price");

fputcsv($fp, $keys);

$bean = new SellableProducts();

$query = $bean->query("prodID");
$query->select->group_by = " prodID ";
$query->select->order_by = " update_date DESC ";

$query->exec();


$cats = new ProductCategoriesBean();


while ($result = $query->nextResult()) {

    $prodID = $result->get("prodID");

    $item = SellableItem::Load($prodID);

    $export_row = array();
    $export_row["id"] = $prodID;
    $export_row["content_id"] = $prodID;
    $export_row["title"] = $item->getTitle();
    $export_row["description"] = $item->getDescription();
    $export_row["availability"] = "in stock";
    $export_row["condition"] = "new";

    $link = LOCAL."/products/details.php?prodID=$prodID";
    $export_row["link"] = fullURL($link);

    $image_link = $item->getMainPhoto()->hrefImage(640,-1);
    $export_row["image_link"] = fullURL($image_link);
    $export_row["brand"] = $item->getBrandName();
    $export_row["product_type"] = $cats->getValue($item->getCategoryID(), "category_name");

    $export_row["price"] = $item->getPriceInfo()->getSellPrice();

    fputcsv($fp, $export_row);

}



?>
