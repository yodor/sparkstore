<?php
$config = BeanConfig::Factory();
$config->contentClass = CheckoutDeliveryAddress::class;
$config->beanClass = ClientAddressesBean::class;
$config->formClass = ClientAddressInputForm::class;
$config->title = tr("Адрес за доставка");
