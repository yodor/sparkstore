<?php
include_once("store/features/shop/templates/modules/checkout/content/CheckoutDeliveryAddress.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/forms/ClientAddressInputForm.php");
$config = BeanConfig::Factory();
$config->contentClass = CheckoutDeliveryAddress::class;
$config->beanClass = ClientAddressesBean::class;
$config->formClass = ClientAddressInputForm::class;
$config->title = tr("Delivery address");
