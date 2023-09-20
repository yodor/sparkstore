<?php
include_once("session.php");
include_once("templates/admin/BeanListPage.php");
include_once("beans/FAQSectionsBean.php");

$bean = new FAQSectionsBean();

$cmp = new BeanListPage();

$cmp->setBean($bean);
$cmp->setListFields(array("section_name"=>"Section"));

$cmp->render();
?>