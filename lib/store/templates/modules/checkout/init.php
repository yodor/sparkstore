<?php
//
// checkout module - the cart and the order of the store sites. Site init.php runs after it and may override.
//
include_once("store/pages/CheckoutTemplatePage.php");
include_once("auth/UserAuthenticator.php");

$module = Module::Active();
if (!$module->pageClass) $module->pageClass = CheckoutTemplatePage::class;
if (!$module->authClass) $module->authClass = UserAuthenticator::class;

//guests: the cart, the fast order and its end. Every other path needs a client - 'login' sends to the account login
$module->addPublicPath("home", "cart", "customer", "complete", "login");
