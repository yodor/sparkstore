<?php
//
// account module - the client area of the store sites. Site init.php runs after it and may override.
//
include_once("store/pages/AccountTemplatePage.php");
include_once("auth/UserAuthenticator.php");

$module = Module::Active();
if (!$module->pageClass) $module->pageClass = AccountTemplatePage::class;
if (!$module->authClass) $module->authClass = UserAuthenticator::class;

//reachable without login - every other path redirects to login before its path file runs
$module->addPublicPath("login", "register", "register/complete", "activate", "password");
