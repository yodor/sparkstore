<?php
include_once("session.php");
include_once("components/templates/admin/BeanListPage.php");

include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductClassAttributesBean.php");


$cmp = new BeanListPage();

$cmp->getPage()->navigation()->clear();


$bean = new ProductClassesBean();

$pattr = new ProductClassAttributesBean();

$sel = new SQLSelect();
$sel->from = " product_classes pc ";
$sel->fields()->set("pc.pclsID", "pc.class_name");
$sel->fields()->setExpression("(SELECT group_concat(a.name SEPARATOR '<BR>') 
FROM product_class_attributes pca JOIN attributes a WHERE a.attrID=pca.attrID AND pca.pclsID=pc.pclsID)", "class_attributes");
$sel->fields()->setExpression("(select group_concat(
concat(
opt.option_name, 
'(',
 (select group_concat(vopt.option_value ORDER BY vopt.position ASC SEPARATOR ';') from variant_options vopt WHERE vopt.parentID=opt.voID ),
 ')'
 )
 ORDER BY opt.position ASC
 SEPARATOR '<BR>' 
 )
 FROM variant_options opt WHERE opt.pclsID = pc.pclsID ORDER BY opt.position ASC)", "class_options");

$cmp->setIterator(new SQLQuery($sel, "pclsID"));
$cmp->setListFields(array("class_name"=>"Class Name", "class_attributes"=>"Class Attributes", "class_options"=>"Class Options"));
$cmp->setBean($bean);


$view = $cmp->initView();

$act = $cmp->viewItemActions();

$act->append(Action::RowSeparator());

$act->append(new Action("Options", "../options/list.php", array(new DataParameter("pclsID"))));

$act->append(Action::RowSeparator());

$act->append(new Action("Attributes", "attributes/list.php", array(new DataParameter("pclsID"))));


$cmp->getPage()->navigation()->clear();
//за изграждане на варианти на продукта от съответния клас

$text = new TextComponent();
$text->addClassName("help summary");
$text->buffer()->start();
echo "Тук може да добавяте класове за назначаване към продуктите.<BR>";
echo "Всеки клас групира набор от входни етикети и опции.<BR>";
echo "Входните етикети позволяват изграждане на допълнителни филтри, освен вградените - по марка и категория, в основния листинг на продуктите.<BR>";
echo "Например за клас 'Книги' подходящи входни етикети биха били Автор и Издател<br>";
echo "Опциите на класа служат за изграждане на варианти на продуктите, които също се използват за филтриране на продуктите.<BR>";
echo "Например опция 'Цвят' или 'Размер'<br>";
$text->buffer()->end();

$cmp->items()->insert($text, 0);
$cmp->render();