<?php
include_once("components/templates/admin/BeanListPage.php");
include_once("store/beans/ContactAddressesBean.php");

$cmp = new BeanListPage();

$cmp->setListFields(array("city"=>"City","address"=>"Address", "phone"=>"Phone","email"=>"Email"));

$cmp->setBean(new ContactAddressesBean());

$cmp->initView();
$cmp->getView()->setDefaultOrder(new OrderColumn("caID", OrderDirection::ASC));

$cmp->getPage()->navigation()->clear();

$cmp->render();