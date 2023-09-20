<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");
include_once("store/forms/VariantParameterInputForm.php");
include_once("store/beans/VariantOptionsBean.php");

$cmp = new BeanEditorPage();
$bean = new VariantOptionsBean();
$req = new BeanKeyCondition($bean, "list.php", array("option_name", "pclsID", "prodID"));
$bean->select()->where()->add("parentID", $req->getID());
$bean->select()->where()->add("option_name", "'".$req->getData("option_name")."'");

$title = tr("Add parameter to option");
if (isset($_GET["editID"])) {
    $title = tr("Edit parameter of option");
}
$cmp->getPage()->setName($title . ": " . $req->getData("option_name"));

$cmp->setBean($bean);
$cmp->setForm(new VariantParameterInputForm());

$cmp->initView();

$pclsID = $req->getData("pclsID");
$prodID = $req->getData("prodID");

$cmp->getEditor()->getTransactor()->assignInsertValue("parentID", $req->getID());
$cmp->getEditor()->getTransactor()->assignInsertValue("option_name", $req->getData("option_name"));
if ($pclsID>0) $cmp->getEditor()->getTransactor()->assignInsertValue("pclsID", $pclsID);
if ($prodID>0) $cmp->getEditor()->getTransactor()->assignInsertValue("prodID", $prodID);


$cmp->render();



?>
