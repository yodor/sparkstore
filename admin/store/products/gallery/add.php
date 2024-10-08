<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductPhotosBean.php");

include_once("forms/PhotoForm.php");

$menu = array();

$rc = new BeanKeyCondition(new ProductsBean(), "../list.php");

$page = new AdminPage();

$page->setName(tr("Image").": ".tr("Product Gallery") . ": " . $rc->getData("product_name"));

$photos = new ProductPhotosBean();
$photos->select()->where()->addURLParameter($rc->getURLParameter());

$view = new BeanFormEditor($photos, new PhotoForm());

$view->getTransactor()->appendURLParameter($rc->getURLParameter());

$view->processInput();

$page->startRender();

$view->render();

$page->finishRender();

?>
