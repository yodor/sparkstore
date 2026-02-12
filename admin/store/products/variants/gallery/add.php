<?php
include_once("session.php");
include_once("components/templates/admin/BeanEditorPage.php");
include_once("store/beans/ProductVariantPhotosBean.php");
include_once("store/beans/ProductVariantsBean.php");

include_once("forms/PhotoForm.php");

$rc = new BeanKeyCondition(new ProductVariantsBean(), "../list.php");

$cmp = new BeanEditorPage();
$cmp->setRequestCondition($rc);

$select = new SQLSelect();
$select->from = " product_variants pv JOIN variant_options opt ON opt.voID = pv.voID ";
$select->where()->add("pvID", (int)$rc->getID());
$select->fields()->set("option_name", "option_value");
$query = new SQLQuery($select, "pvID");
$num = $query->exec();
if ($result = $query->nextResult()) {
    $title = tr("Photo Gallery") . ": " . $result->get("option_name")." - ".$result->get("option_value");
    $cmp->getPage()->setName($title);
}

$photos = new ProductVariantPhotosBean();
$cmp->setBean($photos);
$cmp->setForm(new PhotoForm());
$cmp->render();