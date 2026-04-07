<?php
include_once("store/beans/ProductClassesBean.php");
Template::Condition( new BeanKeyCondition(new ProductClassesBean(), Module::PathURL("/store/classes"), array("class_name")) );

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(ProductClassAttributesBean::class, ProductClassAttributeInputForm::class);

    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) {
        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect content class");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $className = Template::Condition()->getData("class_name");
            $content->config()->title = "Изберете входен етикет за добавяне към клас: " . $className;
            $content->getForm()->setProductClassID(Template::Condition()->getID());
        }
    };


}
else {
    $config = TemplateConfig::List(ProductClassAttributesBean::class);

    $config->listFields = array("pcaID"=>"ID", "name"=>"Входен Етикет");

    $sel = SQLSelect::Table(" product_class_attributes pca LEFT JOIN attributes attr ON attr.attrID = pca.attrID");
    $sel->columns("pca.pcaID", "pca.pclsID", "attr.name", "attr.attrID");
    $sel->where()->match("pca.pclsID", Template::Condition()->getID());

    $config->iterator = new SelectQuery($sel, "pcaID");

    $config->observer = function(TemplateEvent $event) {
        $content = $event->getSource();
        if (!($content instanceof BeanList)) throw new Exception("Event source if not TemplateContent");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $content->config()->title = "Входни етикети към клас: ".Template::Condition()->getData("class_name");
        }
        else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {

            $tableView = $content->tableView();

            $tableView->getColumn("actions")->getCellRenderer()->getActions()->removeByAction("Edit");

            //responders already initialized
            $responder = RequestController::Get("DeleteItemResponder");
            $responder->setConfirmDialogText("Всички продукти от този клас ще загубят съдържанието на етикета. Потвърдете?");

        }

    };

}