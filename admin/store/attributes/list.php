<?php
include_once("session.php");
include_once("components/templates/admin/BeanListPage.php");
include_once("store/beans/AttributesBean.php");

$cmp = new BeanListPage();
$cmp->getPage()->navigation()->clear();

$cmp->setListFields(array("name"=>"Name","unit"=>"Unit", "type"=>"Type"));

$cmp->setBean(new AttributesBean());

$cmp->initView();
$cmp->getView()->setDefaultOrder(" name ASC ");

$cmp->getPage()->navigation()->clear();

$text = new TextComponent();
$text->addClassName("help summary");
$text->buffer()->start();
echo "Тук може да добавяте входни етикет за ползване в продуктовите класове";
$text->buffer()->end();

$cmp->items()->insert($text, 0);
$cmp->render();