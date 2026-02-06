<?php
include_once("session.php");
include_once("auth/UserAuthenticator.php");
include_once("responders/AuthenticatorResponder.php");
include_once("forms/LoginForm.php");
include_once("forms/renderers/LoginFormRenderer.php");

include_once("class/pages/AccountPage.php");


$page = new AccountPage(FALSE);

if ($page->getUserID() > 0) {
    header("Location: orders.php");
    exit;
}

$page->setTitle(tr("Вход клиенти"));

$auth = new UserAuthenticator();

$req = new AuthenticatorResponder($auth);
$req->setCancelUrl(Spark::Get(Config::LOCAL) . "/account/login.php");

$login_redirect = Session::Get("login.redirect", Spark::Get(Config::LOCAL)."/account/orders.php");
$req->setSuccessUrl($login_redirect);


$af = new LoginForm();

$afr = new LoginFormRenderer($af, $req);


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Expires: 0");

$page->startRender();


//echo "<div class='Caption'>" . tr("Вход") . "</div>";

echo "<div class='column login'>";

    echo "<h1 class='Caption'>" . Spark::Get(Config::SITE_TITLE). " - " . tr("вход") . "</h1>";

    echo "<div class='panel'>";
        echo "<div align=center>";
            echo "<div class='login_component'>";
            echo "<span class='inner'>";
            $afr->render();
            echo "</span>";
            echo "</div>"; //login_component
        echo "</div>";// align=center
    echo "</div>"; //panel

echo "</div>"; //column

echo "<div class='column register'>"; //register

    echo "<h1 class='Caption'>" . tr("Все още нямате профил ?") . "</h1>";

    echo "<div class='panel'>";
        echo "<a class='ColorButton' href='register.php'>".tr("Регистрация")."</a>";
    echo "</div>"; //panel

echo "</div>"; //column

$page->finishRender();
?>
