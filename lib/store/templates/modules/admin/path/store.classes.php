<?php

if (URL::Current()->contains("editID")) {
    $config = Template::Editor(ProductClassesBean::class, ProductClassInputForm::class);
}
else {
    $config = Template::List(ProductClassesBean::class);

    $sel = new SQLSelect();
    $sel->from = " product_classes pc ";
    $sel->fields()->set("pc.pclsID", "pc.class_name");
    $sel->fields()->setExpression("(SELECT group_concat(a.name SEPARATOR '<BR>') 
FROM product_class_attributes pca JOIN attributes a WHERE a.attrID=pca.attrID AND pca.pclsID=pc.pclsID)", "class_attributes");
    $sel->fields()->setExpression("(select group_concat(
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

    $config->iterator = new SQLQuery($sel, "pclsID");
    $config->listFields = array("class_name"=>"Class Name", "class_attributes"=>"Class Attributes", "class_options"=>"Class Options");


    $config->summary = "Тук може да добавяте класове за назначаване към продуктите.<BR>
Всеки клас групира набор от входни етикети и опции.<BR>
Входните етикети позволяват изграждане на допълнителни филтри, освен вградените - по марка и категория, в основния листинг на продуктите.<BR>
Например за клас 'Книги' подходящи входни етикети биха били Автор и Издател<br>
Опциите на класа служат за изграждане на варианти на продуктите, които също се използват за филтриране на продуктите.<BR>
Например опция 'Цвят' или 'Размер'<br>";

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