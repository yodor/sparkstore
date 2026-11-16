<?php
$config = TemplateConfig::Factory();
$config->contentClass = CheckoutRowEditor::class;
$config->beanClass = InvoiceDetailsBean::class;
$config->formClass = InvoiceDetailsInputForm::class;
$config->title = tr("Детайли за фактуриране");
