<?php
include_once("session.php");
include_once("templates/admin/NestedSetViewPage.php");

include_once("beans/MenuItemsBean.php");

$cmp = new NestedSetViewPage();

//will use "menu_title" to set the label of the TreeView Item
$cmp->setListFields(array("menu_title" => "MenuTitle"));

$cmp->setBean(new MenuItemsBean());


$cmp->render();



?>
