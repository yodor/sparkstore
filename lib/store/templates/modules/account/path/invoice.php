<?php
include_once("store/templates/modules/account/content/AccountRowEditor.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/forms/InvoiceDetailsInputForm.php");
$config = BeanConfig::Factory();
$config->contentClass = AccountRowEditor::class;
$config->beanClass = InvoiceDetailsBean::class;
$config->formClass = InvoiceDetailsInputForm::class;
$config->title = tr("Детайли за фактуриране");
