<?php
$config = null;
if (URL::Current()->contains("editID")) {

    $config = TemplateConfig::Editor(VariantOptionsBean::class, VariantOptionInputForm::class);

    $config->observer = function(TemplateEvent $event) use($config) {
        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect event source - expected BeanList");

        if (isset($_GET["prodID"]) && (int)$_GET["prodID"] > 0) {
            Template::Condition(BeanKeyCondition::ForBean(ProductsBean::class, Module::PathURL("/store/options"), array("product_name")));
            if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
                $config->title .= " - " . tr("Products") . ": " . Template::Condition()->getData("product_name");
            }
            else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {
                $content->editor()->getTransactor()->assignInsertValue("prodID", Template::Condition()->getID());
            }
        }
        else if (isset($_GET["pclsID"]) && (int)$_GET["pclsID"] > 0) {
            Template::Condition(BeanKeyCondition::ForBean(ProductClassesBean::class, Module::PathURL("/store/options"), array("class_name")));
            if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
                $config->title .= " - " . tr("Class") . ": " . Template::Condition()->getData("class_name");
            }
            else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {
                $content->editor()->getTransactor()->assignInsertValue("pclsID", Template::Condition()->getID());
            }
        }
    };

    $observer_editor = function(BeanTransactorEvent $event) use($config) {
        if (!$event->isEvent(BeanTransactorEvent::BEFORE_COMMIT)) return;

        Debug::ErrorLog("Updating option_name of child items");
        $transactor = $event->getSource();
        if (!($transactor instanceof BeanTransactor)) throw new Exception("Event source is not BeanTransactor");
        if ($transactor->getEditID()<1) return;
        $db = $event->getDB();
        try {
            $newName = $transactor->getValue("option_name");
            $update = SQLUpdate::Table($transactor->getBean()->getTableName());
            $update->set("option_name", $newName);
            $update->where()->match("parentID", $transactor->getEditID());
            $db->query($update)->free();
        }
        catch (Exception $e) {
            throw new Exception("Updating option_name of child items failed: ".$e->getMessage());
        }

    };
    SparkEventManager::register(BeanTransactorEvent::class, new SparkObserver($observer_editor));
}
else {
    $config = TemplateConfig::Factory();
    $config->contentClass = OptionsList::class;
}