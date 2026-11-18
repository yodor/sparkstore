<?php
include_once("store/beans/ProductVariantsBean.php");
include_once("store/beans/ProductVariantPhotosBean.php");
include_once("forms/PhotoForm.php");
include_once("templates/content/BeanEditor.php");
include_once("templates/content/BeanGallery.php");

//back to the variants list of the same product (keeps prodID)
Template::Condition(new BeanKeyCondition(new ProductVariantsBean(), Module::PathURL("/store/products/variants", URL::Current())));

$variantName = (new ProductVariantsBean())->variantName((int)Template::Condition()->getID());

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(ProductVariantPhotosBean::class, PhotoForm::class);
    $config->title = tr("Image") . ": " . $variantName;
}
else {
    $config = BeanGallery::Configure(ProductVariantPhotosBean::class);
    $config->title = tr("Photo Gallery") . ": " . $variantName;
}
$config->useCondition = true;
