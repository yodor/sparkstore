<?php
include_once("session.php");
include_once("pages/AdminLoginPage.php");
include_once("auth/AdminAuthenticator.php");
include_once("responders/AuthenticatorResponder.php");

include_once("forms/LoginForm.php");
include_once("forms/renderers/LoginFormRenderer.php");

$page = new AdminLoginPage();

$auth = new AdminAuthenticator();

$req = new AuthenticatorResponder($auth);
$req->setCancelUrl(ADMIN_LOCAL . "/login.php");
$req->setSuccessUrl(ADMIN_LOCAL . "/index.php");


$af = new LoginForm();

$afr = new LoginFormRenderer($af, $req);

//$afr->getSubmitButton()->setClassName("admin_button orange");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Expires: 0");

$page->startRender();

$page->setTitle(tr("Administration"));

$afr->setCaption(SITE_TITLE . "<BR><small>" . tr("Administration") . "</small>");

$afr->render();

$page->finishRender();
?>