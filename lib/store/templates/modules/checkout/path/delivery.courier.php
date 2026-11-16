<?php
$config = TemplateConfig::Factory();
$config->contentClass = CheckoutDeliveryCourier::class;
$config->beanClass = CourierAddressesBean::class;
$config->formClass = CourierOfficeInputForm::class;
$config->title = tr("Избор на офис на куриер за доставка");
