<?php
include_once("session.php");
include_once("components/templates/admin/BeanListPage.php");
include_once("store/beans/BrandsBean.php");

$cmp = new BeanListPage();
$cmp->getPage()->navigation()->clear();


$cmp->setListFields(array("cover"=>"Cover","brand_name"=>"Brand Name", "summary"=>"Summary", "url"=>"URL", "home_visible"=>"Home Visible"));
$cmp->setBean(new BrandsBean());

$search_fields = array("brand_name", "summary", "brandID");
$cmp->getSearch()->getForm()->setColumns($search_fields);
$cmp->getSearch()->getForm()->getRenderer()->setMethod(FormRenderer::METHOD_GET);

$view = $cmp->initView();

$view->getColumn("home_visible")->setCellRenderer(new BooleanCell("Yes", "No"));

$view->getColumn("cover")->setCellRenderer(new ImageCell());

$cmp->render();