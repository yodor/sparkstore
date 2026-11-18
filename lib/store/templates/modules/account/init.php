<?php
//
// account module - the client area of the store sites. Site init.php runs after it and may override.
//
include_once("class/pages/StorePage.php");
include_once("store/templates/modules/account/content/AccountContent.php");
include_once("auth/UserAuthenticator.php");

$module = Module::Active();
//the site StorePage frame; the account layout is AccountContent
if (!$module->pageClass) $module->pageClass = "StorePage";
$module->addPageCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/account.css");
if (!$module->authClass) $module->authClass = UserAuthenticator::class;

//reachable without login - every other path redirects to login before its path file runs
$module->addPublicPath("login", "register", "register/complete", "activate", "password", "password/reset");

//a client path without login (expired session, a link): the login page says why
$module->loginAlert = "Моля, влезте в профила си";
