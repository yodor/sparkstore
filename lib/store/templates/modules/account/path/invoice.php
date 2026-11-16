<?php
$config = TemplateConfig::Factory();
$config->contentClass = AccountRowEditor::class;
$config->beanClass = InvoiceDetailsBean::class;
$config->formClass = InvoiceDetailsInputForm::class;
$config->title = tr("Детайли за фактуриране");
