<?php
$config = TemplateConfig::Factory();
$config->contentClass = AccountRowEditor::class;
$config->beanClass = ClientAddressesBean::class;
$config->formClass = ClientAddressInputForm::class;
$config->title = tr("Регистриран адрес");
