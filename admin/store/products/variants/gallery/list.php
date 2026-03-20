<?php
include_once("components/templates/admin/GalleryViewPage.php");

include_once("store/beans/ProductVariantPhotosBean.php");
include_once("store/beans/ProductVariantsBean.php");

$rc = new BeanKeyCondition(new ProductVariantsBean(), "../list.php");

$cmp = new GalleryViewPage();
$cmp->setRequestCondition($rc);

$select = SQLSelect::Table(" product_variants pv JOIN variant_options opt ON opt.voID = pv.voID ");

$select->where()->add("pvID", $rc->getID());
$select->set("option_name", "option_value");

$query = new SelectQuery($select, "pvID");
$query->exec();
if ($result = $query->nextResult()) {
    $title = tr("Photo Gallery") . ": " . $result->get("option_name")." - ".$result->get("option_value");
    $cmp->getPage()->setName($title);
}
$query->free();


$bean = new ProductVariantPhotosBean();
$cmp->setBean($bean);

$cmp->render();