<?php
include_once("store/beans/ProductClassesBean.php");
Template::Condition( new BeanKeyCondition(new ProductClassesBean(), Template::PathURL("/store/classes"), array("class_name")) );


if (URL::Current()->contains("editID")) {
    $config = Template::Editor(ProductClassAttributesBean::class, ProductClassAttributeInputForm::class);

    $config->observer = function(TemplateEvent $event) use ($config) {
        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect content class");

        if ($event->isEvent(TemplateEvent::CONTENT_CREATED)) {
            $className = Template::Condition()->getData("class_name");
            $config->title = "Изберете входен етикет за добавяне към клас: " . $className;
        }
        else if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $content->getForm()->setProductClassID(Template::Condition()->getID());
        }
        else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {
            $transactor = $content->editor()->getTransactor();
            $transactor->assignInsertValue("pclsID", Template::Condition()->getID());
        }

    };


}
else {
    $config = Template::List(ProductClassAttributesBean::class);
    $config->summary = "Тук може да добавяте входни етикети към продуктите от избрания клас.<BR>";

    $config->listFields = array("pcaID"=>"ID", "name"=>"Входен Етикет");

    $sel = new SQLSelect();
    $sel->from = " product_class_attributes pca LEFT JOIN attributes attr ON attr.attrID = pca.attrID";
    $sel->fields()->set("pca.pcaID", "pca.pclsID", "attr.name", "attr.attrID");
    $sel->where()->add("pca.pclsID", Template::Condition()->getID());

    $config->iterator = new SQLQuery($sel, "pcaID");

    $config->observer = function(TemplateEvent $event) use ($config) {
        $content = $event->getSource();
        if (!($content instanceof BeanList)) throw new Exception("Event source if not TemplateContent");

        if ($event->isEvent(TemplateEvent::CONTENT_CREATED)) {
            $className = Template::Condition()->getData("class_name");
            $config->title = "Входни етикети към клас: ".$className;
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

Template::Config($config);