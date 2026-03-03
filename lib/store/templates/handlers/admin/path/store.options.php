<?php
$config = null;
if (URL::Current()->contains("editID")) {
    $config = Template::Editor(VariantOptionsBean::class, VariantOptionInputForm::class);
    $config->observer = function(TemplateEvent $event) use($config) {
        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect event source - expected BeanList");

        $prodID = -1;
        $pclsID = -1;

        if (isset($_GET["prodID"])) {
            $prodID = (int)$_GET["prodID"];
        }
        if (isset($_GET["pclsID"])) {
            $pclsID = (int)$_GET["pclsID"];
        }
        if ($prodID>0) {
            Template::Condition(new BeanKeyCondition(new ProductsBean(), Template::PathURL("/store/options"), array("product_name")));
        }
        else if ($pclsID>0) {
            Template::Condition(new BeanKeyCondition(new ProductClassesBean(), Template::PathURL("/store/options"), array("class_name")));
        }

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {

            if ($prodID>0) {
                $content->getBean()->select()->where()->add("prodID", $prodID);
                $config->title .= " - " . tr("Products") . ": " . Template::Condition()->getData("product_name");
            }
            else if ($pclsID>0) {
                $content->getBean()->select()->where()->add("pclsID", $pclsID);
                $config->title .= " - " . tr("Class") . ": " . Template::Condition()->getData("class_name");
            }
            else {
                $content->getBean()->select()->where()->add("parentID" , " NULL ", " IS ");
            }

        }
        else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {

            if ($pclsID>0) {
                $content->editor()->getTransactor()->assignInsertValue("pclsID", $pclsID);
            }
            if ($prodID>0) {
                $content->editor()->getTransactor()->assignInsertValue("prodID", $prodID);
            }

        }
    };
}
else {
    $config = new TemplateConfig();
    $config->summary = "Тук може да добавяте опции за изграждане на продуктови варианти.";
    $config->contentClass = OptionsList::class;

//    $config = Template::List(VariantOptionsBean::class);
//
//    $config->listFields = array("voID"=>"ID", "position"=>"Position", "option_name"=>"Option Name", "parameters"=>"Parameters");
//
//    $config->summary = "Тук може да добавяте опции за изграждане на продуктови варианти.";
//
//    $config->observer = function(TemplateEvent $event) use($config) {
//
//        $content = $event->getSource();
//        if (!($content instanceof BeanList)) throw new Exception("Incorrect event source - expected BeanList");
//
//        $prodID = -1;
//        $pclsID = -1;
//
//        if (isset($_GET["prodID"])) {
//            $prodID = (int)$_GET["prodID"];
//        }
//        if (isset($_GET["pclsID"])) {
//            $pclsID = (int)$_GET["pclsID"];
//        }
//
//    };
}
Template::Config($config);