<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");

$page = new CheckoutPage();

$orderID = 0;
if (isset($_GET["orderID"])) {
    $orderID = (int)$_GET["orderID"];
}

$page->setTitle(tr("Завършена поръчка"));

$page->startRender();



echo "<div class='column'>";

    echo "<div class='Caption'>" . tr("Завършена поръчка") . "</div>";

    echo "<div class='success_message'>";

        echo "<div class='tick_mark'></div>";

        echo "<div class='Caption'>".tr("Благодарим Ви че пазарувахте при нас!")."</div>";

        if ($orderID>0) {
            echo tr("Номер на поръчката") . ": " . $orderID;
            echo "<BR><BR>";
        }

        echo tr("Ще се свържем с Вас относно детйали за Вашата поръчка");

    echo "</div>"; // success_message

echo "</div>"; //column


$page->finishRender();
?>
