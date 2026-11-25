<?php
include_once("store/templates/modules/checkout/content/CheckoutRowEditor.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/forms/InvoiceDetailsInputForm.php");
$config = BeanConfig::Factory();
$config->contentClass = CheckoutRowEditor::class;
$config->beanClass = InvoiceDetailsBean::class;
$config->formClass = InvoiceDetailsInputForm::class;
$config->title = tr("Invoice details");
