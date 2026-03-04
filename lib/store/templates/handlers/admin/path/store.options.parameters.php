<?php
include_once("store/beans/VariantOptionsBean.php");
Template::Condition(new BeanKeyCondition(new VariantOptionsBean(), Template::PathURL("/store/options"), array("option_name", "pclsID", "prodID")));

$config = null;
if (URL::Current()->contains("editID")) {
    //
    $config = Template::Editor(VariantOptionsBean::class, VariantParameterInputForm::class);

    $config->observer = function(TemplateEVent $event) use($config) {
        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) throw new Exception("Incorrect event source - expected BeanEditor");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            $config->title = tr("Parameter editor for option") . ": " . Template::Condition()->getData("option_name");
        }
        else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {

            $transactor = $content->editor()->getTransactor();

            $bean = $content->getBean();

            //setting parentID means it is a parameter and not the option itself
            $transactor->assignInsertValue("parentID", Template::Condition()->getID());
            $bean->select()->where()->add("parentID", Template::Condition()->getID());

            //copy the option name to this parameter
            $transactor->assignInsertValue("option_name", Template::Condition()->getData("option_name"));
            $transactor->assignUpdateValue("option_name", Template::Condition()->getData("option_name"));

            //copy pclsID - if set - parent is class specific option
            $pclsID = Template::Condition()->getData("pclsID");
            if ($pclsID>0) {
                $transactor->assignInsertValue("pclsID", $pclsID);
                $bean->select()->where()->add("pclsID", $pclsID);
            }

            //copy prodID - if set - parent is product specific option
            $prodID = Template::Condition()->getData("prodID");
            if ($prodID>0) {
                $transactor->assignInsertValue("prodID", $prodID);
                $bean->select()->where()->add("pclsID", $prodID);
            }

            if ($bean instanceof OrderedDataBean) {
//                $selectMax = clone $bean->select();
//                $selectMax->where()->add("parentID", Template::Condition()->getID());
                $maxPosition = $bean->getMaxPosition() + 1;
                $transactor->assignInsertValue("position", $maxPosition);
            }
        }
    };

}
else {
    $config = Template::List(VariantOptionsBean::class);
    $config->listFields = array("voID"=>"ID", "position"=>"Position", "option_value"=>"Parameter Name");

    $config->observer = function(TemplateEvent $event) use($config) {
        $content = $event->getSource();
        if (!($content instanceof BeanList)) throw new Exception("Content is not BeanList");

        if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
            //limit bean queries - max position selector etc
            $content->getBean()->select()->where()->add("parentID" , Template::Condition()->getID());

            //changes to bean->select do not reflect to content->iterator after next call
            $content->setIterator($content->getBean()->queryFull());

            $config->title = tr("Parameters for option") . ": " . Template::Condition()->getData("option_name");

        }
    };


    $config->summary = "Тук добавяте избираеми параметри за съответната опция.<BR>
Параметрите се използват за избор от клиента преди поръчка на продукт.<BR>";


}

Template::SetConfig($config);