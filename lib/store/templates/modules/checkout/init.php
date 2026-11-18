<?php
//
// checkout module - the cart and the order of the store sites. Site init.php runs after it and may override.
//
include_once("store/templates/modules/checkout/content/CheckoutContent.php");
include_once("auth/UserAuthenticator.php");

$module = Module::Active();
//the site StorePage frame; the checkout layout is CheckoutContent
if (!$module->pageClass) $module->pageClass = "StorePage";
$module->addPageClass("CheckoutPage");
$module->addPageCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/checkout.css");
if (!$module->authClass) $module->authClass = UserAuthenticator::class;

//guests: the cart, the guest page (fast order or the account login - every path that needs a client sends there)
//and the end of the order
$module->loginPath = "guest";
$module->addPublicPath("home", "cart", "guest", "complete");

//a guest at the cashier goes to the guest page - a normal step, no alert
$module->loginAlert = "";
