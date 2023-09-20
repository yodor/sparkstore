<?php
include_once("session.php");
include_once("class/pages/AccountPage.php");
include_once("beans/UsersBean.php");

$page = new AccountPage(FALSE);

$page->setTitle(tr("Успешна регистрация"));

$page->startRender();

echo "<div class='column'>";

    echo "<h1 class='Caption'>".tr("Успешна регистрация")."</h1>";

    echo "<div class='success_message'>";
        echo "<div class='tick_mark'></div>";

        echo "<h1 class='Caption'>".tr("Благодарим Ви че се регистрирахте при нас")."</h1>";

        echo "<span>".tr("Ще получите e-mail за активация на профила си.")."</span>";
        echo "<span>".tr("След активация ще можете да променяте своята лична информация и адрес за доставка.")."</span>";

    echo "</div>";

echo "</div>";

$page->finishRender();
?>
