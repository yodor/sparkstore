<?php
include_once("session.php");
include_once("components/templates/admin/BeanEditorPage.php");
include_once("store/forms/ContactAddressInputForm.php");
include_once("store/beans/ContactAddressesBean.php");


$cmp = new BeanEditorPage();

$cmp->setBean(new ContactAddressesBean());
$cmp->setForm(new ContactAddressInputForm());
$cmp->render();