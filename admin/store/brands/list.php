<?php
include_once("session.php");
include_once("templates/admin/BeanListPage.php");
include_once("store/beans/BrandsBean.php");

$cmp = new BeanListPage();
$cmp->setListFields(array("cover"=>"Cover","brand_name"=>"Brand Name", "summary"=>"Summary", "url"=>"URL", "home_visible"=>"Home Visible"));
$cmp->setBean(new BrandsBean());

$search_fields = array("brand_name", "summary", "brandID");
$cmp->getSearch()->getForm()->setFields($search_fields);
$cmp->getSearch()->getForm()->getRenderer()->setMethod(FormRenderer::METHOD_GET);

$view = $cmp->initView();

$view->getColumn("home_visible")->setCellRenderer(new BooleanCellRenderer("Yes", "No"));

$view->getColumn("cover")->setCellRenderer(new ImageCellRenderer());

$cmp->render();

?>
