<?php
include_once("session.php");

include_once("class/pages/AdminPage.php");

include_once("store/mailers/OrderConfirmationMailer.php");

$page = new AdminPage();
$page->startRender();

if (!isset($_GET["orderID"])) {
    throw new Exception("OrderID not set");
}
$orderID = (int)$_GET["orderID"];

$mailer = new OrderConfirmationMailer($orderID);
$mailer->send();

$page->finishRender();

?>