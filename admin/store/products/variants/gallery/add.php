<?php
include_once("components/templates/admin/BeanEditorPage.php");
include_once("store/beans/ProductVariantPhotosBean.php");
include_once("store/beans/ProductVariantsBean.php");

include_once("forms/PhotoForm.php");

$rc = new BeanKeyCondition(new ProductVariantsBean(), "../list.php");

$cmp = new BeanEditorPage();
$cmp->setRequestCondition($rc);

$select = new SQLSelect();
$select->from("product_variants pv")->join("variant_options opt")->on("opt.voID = pv.voID ");
$select->where()->match("pvID", $rc->getID());
$select->columns("option_name", "option_value");

$query = new SelectQuery($select, "pvID");
$query->exec();
if ($result = $query->nextResult()) {
    $title = tr("Photo Gallery") . ": " . $result->get("option_name")." - ".$result->get("option_value");
    $cmp->getPage()->setName($title);
}
$query->free();

$photos = new ProductVariantPhotosBean();
$cmp->setBean($photos);
$cmp->setForm(new PhotoForm());
$cmp->render();