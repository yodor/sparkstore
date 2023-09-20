<?php
include_once("session.php");
include_once("templates/admin/NestedSetViewPage.php");
include_once("store/beans/ProductCategoriesBean.php");

$cmp = new NestedSetViewPage();

$cmp->setBean(new ProductCategoriesBean());
$cmp->setListFields(array("category_name"=>"Category Name"));

$view = $cmp->initView();
$view->setBranchRenderMode(NestedSetTreeView::MODE_BRANCHES_UNFOLDED);
$iterator = $view->getIterator();
if ($iterator instanceof SQLQuery) {
    $iterator->select->fields()->setExpression("(SELECT pcp.pcpID FROM product_category_photos pcp WHERE pcp.catID = node.catID ORDER BY pcp.position ASC LIMIT 1)", " pcpID ");
}
$item = $view->getItemRenderer();
if ($item instanceof TextTreeItem) {
    $si = new StorageItem();
    $si->className = "ProductCategoryPhotosBean";
    $si->setName("pcpID");

    $item->setPhotoSize(32, 32);
    $item->setIcon($si);
}

$cmp->render();


?>
