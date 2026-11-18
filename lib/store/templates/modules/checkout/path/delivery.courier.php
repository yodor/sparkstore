<?php
include_once("store/templates/modules/checkout/content/CheckoutDeliveryCourier.php");
include_once("store/beans/CourierAddressesBean.php");
include_once("store/forms/CourierOfficeInputForm.php");
$config = BeanConfig::Factory();
$config->contentClass = CheckoutDeliveryCourier::class;
$config->beanClass = CourierAddressesBean::class;
$config->formClass = CourierOfficeInputForm::class;
$config->title = tr("Избор на офис на куриер за доставка");
