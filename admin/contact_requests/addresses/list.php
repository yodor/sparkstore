<?php
include_once("session.php");
include_once("templates/admin/BeanListPage.php");
include_once("store/beans/ContactAddressesBean.php");

$cmp = new BeanListPage();

$cmp->setListFields(array("city"=>"City","address"=>"Address", "phone"=>"Phone","email"=>"Email"));

$cmp->setBean(new ContactAddressesBean());

$cmp->initView();
$cmp->getView()->setDefaultOrder(" caID ASC ");

$cmp->getPage()->navigation()->clear();

$cmp->render();


?>
