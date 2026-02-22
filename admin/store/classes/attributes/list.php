<?php
include_once("components/templates/admin/BeanListPage.php");

include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductClassAttributesBean.php");


$cmp = new BeanListPage();

$bean = new ProductClassesBean();
$caBean = new ProductClassAttributesBean();

$req = new BeanKeyCondition($bean, "../list.php", array("class_name"));
$className = $bean->getValue($req->getID(), "class_name");

$cmp->getPage()->setName(tr("Входни етикети към клас: ").$className);

$sel = new SQLSelect();
$sel->from = " product_class_attributes pca LEFT JOIN attributes attr ON attr.attrID = pca.attrID";
$sel->fields()->set("pca.pcaID", "pca.pclsID", "attr.name", "attr.attrID");
$sel->where()->add("pca.pclsID", $req->getID());
$cmp->setIterator(new SQLQuery($sel, "pcaID"));


$cmp->setListFields(array("pcaID"=>"ID", "name"=>"Входен Етикет"));
$cmp->setBean($caBean);

$view = $cmp->initView();
$act = $cmp->viewItemActions();
$act->removeByAction("Edit");

//responders already initialized
$responder = RequestController::Get("DeleteItemResponder");
$responder->setConfirmDialogText("Всички продукти от този клас ще загубят съдържанието на етикета. Потвърдете?");

$text = new TextComponent();
$text->addClassName("help summary");
$text->buffer()->start();
echo "Тук може да добавяте входни етикети към продуктите от избрания клас.<BR>";
$text->buffer()->end();

$cmp->items()->insert($text, 0);
$cmp->render();