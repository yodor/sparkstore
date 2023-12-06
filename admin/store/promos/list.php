<?php
include_once("session.php");

include_once("templates/admin/BeanListPage.php");
include_once("store/beans/StorePromosBean.php");
include_once("store/beans/ProductCategoriesBean.php");

include_once("components/renderers/cells/ImageCellRenderer.php");
include_once("components/renderers/cells/BooleanCellRenderer.php");


$cmp = new BeanListPage();

$bean = new StorePromosBean();
$product_categories = new ProductCategoriesBean();


$cmp->setListFields(array("start_date"=>"Start Date", "end_date"=>"End Date", "target"=>"Category Target", "targetID" => "Category ID", "discount_percent"=>"Discount Percent"));

$cmp->setBean($bean);

$cmp->initView();

$cmp->getView()->getColumn("targetID")->setCellRenderer(new CallbackCellRenderer("renderCategory"));

$cmp->getPage()->navigation()->clear();

$cmp->render();

function renderCategory(array $row, TableColumn $tc)
{
    global $product_categories;

    $parentNodes = $product_categories->getParentNodes($row["targetID"], array("category_name"));

    $names = array();
    foreach ($parentNodes as $idx => $data) {
        $names[] = $data["category_name"];
    }
    $category = implode(" // ", $names);

    echo $category;
}


?>
