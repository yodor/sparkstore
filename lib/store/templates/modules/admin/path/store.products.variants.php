<?php
include_once("store/beans/ProductsBean.php");
include_once("store/templates/content/ProductVariantsList.php");

Template::Condition(new BeanKeyCondition(new ProductsBean(), Module::PathURL("/store/products"), array("product_name")));

$config = TemplateConfig::Factory();
$config->contentClass = ProductVariantsList::class;
$config->title = tr("Product Variants") . ": " . Template::Condition()->getData("product_name");
