<?php
//
// pages module - store part, included after the core init.php: the storefront look
//
include_once("store/pages/StorePagesPage.php");

$module = Module::Active();
$module->pageClass = StorePagesPage::class;
