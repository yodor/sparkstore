<?php
include_once("session.php");
include_once("components/templates/admin/BeanListPage.php");
include_once("store/beans/SectionsBean.php");

$cmp = new BeanListPage();

$cmp->getPage()->navigation()->clear();


$cmp->setListFields(array("position"=>"#", "section_title"=>"Section"));

$bean = new SectionsBean();
$cmp->setBean($bean);

$cmp->initView();
//$cmp->getView()->setDefaultOrder(" position ASC ");
$cmp->viewItemActions()->append(Action::RowSeparator());
$cmp->viewItemActions()->append(new Action("Banners Gallery", "banners/list.php", array(new DataParameter("secID", $bean->key()))));
$cmp->viewItemActions()->append(Action::RowSeparator());
$cmp->viewItemActions()->append(
    new Action("Products", Spark::Get(Config::ADMIN_LOCAL)."/store/products/list.php",
        array(
           new DataParameter("filter_section", "section_title"),
           new URLParameter("filter", "search"),
        )
    )
);


$text = new TextComponent();
$text->addClassName("help summary");
$text->buffer()->start();
echo "Тук добавяте секции за изграждане на списъци от продукти за извеждане в началната страница.<BR>";
$text->buffer()->end();

$cmp->items()->insert($text, 0);
$cmp->render();