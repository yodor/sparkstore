<?php
include_once("store/beans/ProductClassesBean.php");
include_once("templates/content/BeanEditor.php");
include_once("store/beans/ProductClassAttributesBean.php");
include_once("store/forms/ProductClassAttributeInputForm.php");
include_once("templates/content/BeanList.php");
Template::Condition( new BeanKeyCondition(new ProductClassesBean(), Module::PathURL("/store/classes"), array("class_name")) );

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(ProductClassAttributesBean::class, ProductClassAttributeInputForm::class);

    $config->useCondition = true;

    $config->observer = function(TemplateEvent $event) {
        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect content class");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $className = Template::Condition()->getData("class_name");
            $content->config()->title = tr("Choose an input label to add to the class: ") . $className;
            $content->getForm()->setProductClassID(Template::Condition()->getID());
        }
    };


}
else {
    $config = BeanList::Configure(ProductClassAttributesBean::class);

    $config->listFields = array("pcaID"=>"ID", "name"=>"Input Label");

    $sel = SQLSelect::Table(" product_class_attributes pca LEFT JOIN attributes attr ON attr.attrID = pca.attrID");
    $sel->columns("pca.pcaID", "pca.pclsID", "attr.name", "attr.attrID");
    $sel->where()->match("pca.pclsID", Template::Condition()->getID());

    $config->iterator = new SelectQuery($sel, "pcaID");

    $config->observer = function(TemplateEvent $event) {
        $content = $event->getSource();
        if (!($content instanceof BeanList)) throw new Exception("Event source if not TemplateContent");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $content->config()->title = tr("Input labels of the class: ") . Template::Condition()->getData("class_name");
        }
        else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {

            $tableView = $content->tableView();

            $tableView->getColumn("actions")->getCellRenderer()->getActions()->removeByAction("Edit");

            //responders already initialized
            $responder = RequestController::Get("DeleteItemResponder");
            $responder->setConfirmDialogText("All the products of this class will lose the contents of the label. Confirm?");

        }

    };

}