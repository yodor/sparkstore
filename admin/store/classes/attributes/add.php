<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");

include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductClassAttributesBean.php");
include_once("store/forms/ProductClassAttributeInputForm.php");

$cmp = new BeanEditorPage();
$cmp->getPage()->setName("Изберете входен етикет за добавяне към класа");

$bean = new ProductClassesBean();
$req = new BeanKeyCondition($bean, "../list.php", array("class_name"));
$className = $bean->getValue($req->getID(), "class_name");
$pclsID = $req->getID();;

$cmp->setBean(new ProductClassAttributesBean());

$form = new ProductClassAttributeInputForm();
$form->setProductClassID($pclsID);
$cmp->setForm($form);

$closure = function(BeanFormEditorEvent $event) use ($pclsID) {
    if ($event->isEvent(BeanFormEditorEvent::EDITOR_CREATED)) {
        debug("Processing BeanFormEditorEvent::EDITOR_CREATED ...");
        $editor = $event->getSource();
        if (!($editor instanceof BeanFormEditor)) throw new Exception("Event source is not BeanFormEditor");

        $transactor = $editor->getTransactor();
        $transactor->assignInsertValue("pclsID", $pclsID);
    }
};
SparkEventManager::register(BeanFormEditorEvent::class, new SparkObserver($closure));


$cmp->render();

?>
