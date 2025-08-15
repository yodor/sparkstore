<?php
include_once("session.php");
include_once("templates/admin/DynamicPageList.php");

$cmp = new DynamicPageList();

if (!$cmp->isChooser()) {
    $cmp->getPage()->navigation()->clear();
}

$cmp->render();

?>
