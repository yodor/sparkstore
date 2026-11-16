<?php
//
// news module - store part, included after the core init.php: the storefront look
//
include_once("store/pages/StoreModulePage.php");

$module = Module::Active();
$module->pageClass = StoreModulePage::class;
