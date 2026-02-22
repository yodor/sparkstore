<?php
include_once("components/templates/admin/DynamicPageList.php");

$cmp = new DynamicPageList();

if (!$cmp->isChooser()) {
    $cmp->getPage()->navigation()->clear();
}

$cmp->render();