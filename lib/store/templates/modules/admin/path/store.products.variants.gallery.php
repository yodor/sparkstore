<?php
include_once("store/beans/ProductVariantsBean.php");
include_once("store/beans/ProductVariantPhotosBean.php");
include_once("forms/PhotoForm.php");

//back to the variants list of the same product (keeps prodID)
Template::Condition(new BeanKeyCondition(new ProductVariantsBean(), Module::PathURL("/store/products/variants", URL::Current())));

$variantName = (new ProductVariantsBean())->variantName((int)Template::Condition()->getID());

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(ProductVariantPhotosBean::class, PhotoForm::class);
    $config->title = tr("Image") . ": " . $variantName;
}
else {
    $config = TemplateConfig::Gallery(ProductVariantPhotosBean::class);
    $config->title = tr("Photo Gallery") . ": " . $variantName;
}
$config->useCondition = true;
