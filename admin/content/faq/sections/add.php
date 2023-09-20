<?php
include_once("session.php");
include_once("templates/admin/BeanEditorPage.php");

include_once("forms/FAQSectionInputForm.php");
include_once("beans/FAQSectionsBean.php");


$cmp = new BeanEditorPage();
$cmp->setBean(new FAQSectionsBean());
$cmp->setForm(new FAQSectionInputForm());
$cmp->render();

?>