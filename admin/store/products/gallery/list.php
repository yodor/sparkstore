<?php
include_once("class/pages/AdminPage.php");

include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductsBean.php");

include_once("components/GalleryView.php");

$menu = array();

$page = new AdminPage();

$rc = new BeanKeyCondition(new ProductsBean(), "../list.php", array("product_name"));

$page->setName(tr("Product Gallery") . ": " . $rc->getData("product_name"));

$bean = new ProductPhotosBean();
$bean->select()->where()->addURLParameter($rc->getURLParameter());

$h_delete = new DeleteItemResponder($bean);

$h_repos = new ChangePositionResponder($bean);

$gv = new GalleryView($bean);

$url = URL::Current();
$url->setScriptName("add.php");
$action_add = new Action(SparkAdminPage::ACTION_ADD, $url->toString());
$action_add->setTooltip("Add new element to this collection");
$page->getActions()->append($action_add);

$gv->getItemActions()->addURLParameter($rc->getURLParameter());

Session::Set("products.gallery", URL::Current()->toString());

$page->setPageMenu($menu);
$page->startRender();

$gv->render();

$page->finishRender();