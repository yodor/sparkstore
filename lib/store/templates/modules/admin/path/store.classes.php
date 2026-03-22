<?php

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(ProductClassesBean::class, ProductClassInputForm::class);
}
else {
    $config = TemplateConfig::List(ProductClassesBean::class);

    $sel = SQLSelect::Table(" product_classes pc ");

    $sel->columns("pc.pclsID", "pc.class_name");
    $sel->alias("(SELECT group_concat(a.name SEPARATOR '<BR>') 
FROM product_class_attributes pca JOIN attributes a WHERE a.attrID=pca.attrID AND pca.pclsID=pc.pclsID)", "class_attributes");
    $sel->alias("(select group_concat(
concat(
opt.option_name, 
'<BR>[',
 (select group_concat(vopt.option_value ORDER BY vopt.position ASC SEPARATOR '<BR>') from variant_options vopt WHERE vopt.parentID=opt.voID ),
 ']<BR>'
 )
 ORDER BY opt.position ASC
 SEPARATOR '<BR>' 
 )
 FROM variant_options opt WHERE opt.pclsID = pc.pclsID ORDER BY opt.position ASC)", "class_options");

    $config->iterator = new SelectQuery($sel, "pclsID");
    $config->listFields = array("class_name"=>"Class Name", "class_attributes"=>"Class Attributes", "class_options"=>"Class Options");

    $config->observer = function(TemplateEvent $event) {

        if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;

        $content = $event->getSource();
        if (!($content instanceof BeanList)) throw new Exception("Incorrect event source - expecting BeanList");

        $actions = $content->getItemActions()->getActions();
        $actions->append(Action::RowSeparator());

        $optionsAction = TemplateContent::CreateAction("Options", "Options", "/store/options");
        $optionsAction->getURL()->add(new DataParameter("pclsID"));
        $actions->append($optionsAction);

        $actions->append(Action::RowSeparator());

        $attributesAction = TemplateContent::CreateAction("Attributes", "Attributes", "attributes");
        $attributesAction->getURL()->add(new DataParameter("pclsID"));

        $actions->append($attributesAction);
    };

    $config->clearNavigation = true;
}