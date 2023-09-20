<?php
include_once("session.php");
include_once("templates/admin/BeanListPage.php");
include_once("store/beans/ContactRequestsBean.php");
include_once("components/renderers/cells/ClosureCellRenderer.php");

$bean = new ContactRequestsBean();


$menu = array(
//    new MenuItem("Settings", "config/list.php", "code-class.png"),
    new MenuItem("Adresses", "addresses/list.php", "code-class.png"),
);





$cmp = new BeanListPage();

$cmp->getPage()->setPageMenu($menu);

$cmp->setBean($bean);
$cmp->setListFields(array("fullname"=>"Name","phone"=>"Phone", "email"=>"Email", "query"=>"Query","date_created"=>"Date"));

$view = $cmp->initView();

//$product_link = function($row, TableColumn $tc) {
//
//    $value = (int)$row[$tc->getFieldName()];
//    if ($value>0) {
//        echo "<a href='" . LOCAL . "/details.php?prodID=$value'>$value</a>";
//    }
//};

//$cmp->getView()->getColumn("prodID")->setCellRenderer(new ClosureCellRenderer($product_link));

$cmp->viewItemActions()->removeByAction("Edit");

$cmp->getPage()->getActions()->removeByAction("Add");

$cmp->getPage()->navigation()->clear();
$cmp->render();

?>
