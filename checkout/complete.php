<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");

$page = new CheckoutPage();

$orderID = 0;
if (isset($_GET["orderID"])) {
    $orderID = (int)$_GET["orderID"];
}

$page->setTitle(tr("Завършена поръчка"));

$tickMark = new Component(false);
$tickMark->setComponentClass("tick_mark");
$page->base()->items()->append($tickMark);

$caption = new Component(false);
$caption->setComponentClass("caption");
$caption->setContents(tr("Благодарим Ви че пазарувахте при нас!"));
$page->base()->items()->append($caption);

if ($orderID > 0) {
    $orderNumber = new LabelSpan();
    $orderNumber->addClassName("order_number");
    $orderNumber->label()->setContents(tr("Номер на поръчката"));
    $orderNumber->span()->setContents($orderID);
    $page->base()->items()->append($orderNumber);
}

$page->base()->items()->append(new TextComponent(tr("Ще се свържем с Вас относно детйали за Вашата поръчка")));

$page->render();
?>
