<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");

$page = new CheckoutPage();

$page->ensureCartItems();
$page->ensureClient();

$cart = Cart::Instance();

$courier = $cart->getDelivery()->getSelectedCourier();

$courier_id = $courier->getID();

include_once("courier_office.php");