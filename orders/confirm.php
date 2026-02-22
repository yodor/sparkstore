<?php
include_once("class/pages/ProductsPage.php");

include_once("store/beans/OrdersBean.php");

include_once("store/forms/OrderAddressInputForm.php");
include_once("beans/UsersBean.php");
include_once("store/beans/UserDetailsBean.php");
include_once("auth/Authenticator.php");

include_once("store/mailers/RegisterDetailsPasswordMailer.php");
include_once("store/mailers/OrderConfirmedAdminMailer.php");
include_once("store/forms/processors/OrderConfirmProcessor.php");

$page = new ProductsPage();

$confirm_success = FALSE;
$error_details = "";

ob_start();
$confirm_proc = new ConfirmOrder();
try {
    $confirm_proc->process();
    $confirm_success = TRUE;
}
catch (Exception $e) {
    $error_details = $e->getMessage();
    //   Session::Alert(tr($error_details));
}
ob_end_clean();

$page->startRender();

echo "<div class='column left'>";
$page->renderCategoryTree();
$page->renderNewsItems();

echo "</div>";

echo "<div class='column right orders'>";

echo "<div class='Caption'>";
echo tr("Потвърждаване на поръчка");
echo "</div>";

if ($confirm_success) {

    echo "<div class='panel'>";

    echo "<div class='message success'>";
    echo tr("Вие потвърдихте успешно Вашата поръчка.");
    echo "</div>";

    echo "</div>";

    $page->renderOrderDetails($confirm_proc->orderID, $confirm_proc->confirmed_order, $confirm_proc->confirm_ticket);

    $page->renderOrderDeliveryAddress($confirm_proc->confirmed_order);

    //increment order_counter
    ob_start();
    $items = explode("\r\n", $confirm_proc->confirmed_order["cart_data"]);
    foreach ($items as $key => $item) {
        $cart_row = explode("|", $item);
        $prod = $cart_row[1];
        $prod = explode(":", $prod);
        $prodID = (int)$prod[1];

        try {
            include_once("store/beans/ProductsBean.php");
            $products = new ProductsBean();
            $prod_row = $products->getByID($prodID);
            $order_counter = (int)$prod_row["order_counter"];
            $order_counter++;
            $update_row["order_counter"] = $order_counter;
            $products->update($prodID, $update_row);
        }
        catch (Exception $e) {
            continue;
        }
    }
    ob_end_clean();
    ///////

}
else {
    echo "<div class='panel'>";

    echo "<div class='message error'>";
    echo tr("Възникна грешка при опит за потвърждаване на Вашата поръчка.");
    echo "</div>";

    echo "</div>";

    echo "<div class='Caption'>";
    echo tr("Детайли");
    echo "</div>";

    echo "<div class='panel error_details'>";

    echo tr("Моля изчакайте малко и презаредете тази страница.");
    echo "<BR>";
    echo tr("Ако все още има проблем с потвърждаването на поръчката, моля свържете се с нас.");
    echo "<BR>";
    $contactsURL = new URL(Spark::Get(Config::LOCAL) . "/contacts.php");
    echo "<a href='{$contactsURL->fullURL()}'>" . tr("Продължи към страницата за контакти.") . "</a>";
    echo "<BR><BR>";

    echo tr("Код за потвърждение на поръчка") . ": ";
    echo "<div class='confirm_ticket'>" . $confirm_proc->confirm_ticket . "</div>";

    echo "<BR>";

    echo tr("Детайлизирано съобшение за грешка") . ": ";
    echo "<BR>";

    echo "<div class='error_message'>";
    echo tr($error_details);
    echo "</div>";

    echo "</div>";
}

echo "</div>";

$page->finishRender();