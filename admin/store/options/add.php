<?php
include_once("components/templates/admin/BeanEditorPage.php");
include_once("store/forms/VariantOptionInputForm.php");
include_once("store/beans/VariantOptionsBean.php");
include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductsBean.php");

$cmp = new BeanEditorPage();
$title = tr("Add Option");

$bean = new VariantOptionsBean();
$bean->select()->where()->add("parentID" , " NULL ", " IS ");

$prodID = -1;
$pclsID = -1;

if (isset($_GET["prodID"])) {
    $prodID = (int)$_GET["prodID"];
}
$products = new ProductsBean();
if ($prodID>0) {
    try {
        $product_data = $products->getByID($prodID, "product_name");
        $bean->select()->where()->add("prodID", $prodID);
        $title .= " - " . tr("Products") . ": " . $product_data["product_name"];
    }
    catch (Exception $e) {
        Session::SetAlert("Product unaccessible");
    }
}
else {
    //options for class
    if (isset($_GET["pclsID"])) {
        $pclsID = (int)$_GET["pclsID"];
    }

    $classes = new ProductClassesBean();
    if ($pclsID > 0) {
        try {
            $class_data = $classes->getByID($pclsID, "class_name");
            $bean->select()->where()->add("pclsID", $pclsID);
            $title .= " - " . tr("Class") . ": " . $class_data["class_name"];
        }
        catch (Exception $e) {
            Session::SetAlert("Class unaccessible");
        }
    }

}

$cmp->getPage()->setName($title);

$cmp->setBean($bean);
$cmp->setForm(new VariantOptionInputForm());
$cmp->initView();

if ($pclsID>0) {
    $cmp->getEditor()->getTransactor()->assignInsertValue("pclsID", $pclsID);
}
if ($prodID>0) {
    $cmp->getEditor()->getTransactor()->assignInsertValue("prodID", $prodID);
}
$cmp->render();