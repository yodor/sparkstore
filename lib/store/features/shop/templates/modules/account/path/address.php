<?php
include_once("store/features/shop/templates/modules/account/content/AccountRowEditor.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/forms/ClientAddressInputForm.php");
$config = BeanConfig::Factory();
$config->contentClass = AccountRowEditor::class;
$config->beanClass = ClientAddressesBean::class;
$config->formClass = ClientAddressInputForm::class;
$config->title = tr("Registered address");
