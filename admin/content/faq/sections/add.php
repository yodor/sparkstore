<?php
include_once("components/templates/admin/BeanEditorPage.php");

include_once("forms/FAQSectionInputForm.php");
include_once("beans/FAQSectionsBean.php");


$cmp = new BeanEditorPage();
$cmp->setBean(new FAQSectionsBean());
$cmp->setForm(new FAQSectionInputForm());
$cmp->render();