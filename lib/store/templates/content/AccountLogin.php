<?php
include_once("store/templates/content/AccountContent.php");
include_once("responders/AuthenticatorResponder.php");
include_once("forms/LoginForm.php");
include_once("forms/renderers/LoginFormRenderer.php");

/**
 * Client login and the link to the registration.
 * After login: the session 'login.redirect' (checkout sets it) or the account home.
 */
class AccountLogin extends AccountContent
{
    public function initialize() : void
    {
        //logged in already
        if (Module::Active()->getAuthContext()) $this->redirect("home");

        $authenticator = Module::Active()->getAuthenticator();
        if (is_null($authenticator)) throw new Exception("No module Authenticator initialized");

        $responder = new AuthenticatorResponder($authenticator);
        $responder->setCancelUrl(Module::PathURL("login")->toString());
        $responder->setSuccessUrl((string)Session::Get("login.redirect", Module::PathURL("home")->toString()));

        $form = new LoginFormRenderer(new LoginForm(), $responder::class, $authenticator->produceLoginToken());
        $action = $form->getTextSpace()->items()->getByAction(LoginFormRenderer::ACTION_PASSWORD);
        if ($action instanceof Action) {
            $action->setURL(Module::PathURL("password"));
        }

        $login = new Container(false);
        $login->setComponentClass("login_component");
        $inner = new Container(false);
        $inner->setComponentClass("inner");
        $inner->setTagName("span");
        $inner->items()->append($form);
        $login->items()->append($inner);

        $this->addColumn(AccountContent::Column(Spark::Get(Config::SITE_TITLE) . " - " . tr("вход"), $login, "login"));

        $register = new Action();
        $register->setURL(Module::PathURL("register"));
        $register->setClassName("ColorButton");
        $register->setText(tr("Регистрация"));
        $this->addColumn(AccountContent::Column(tr("Все още нямате профил ?"), $register, "register"));

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Expires: 0");
    }

    protected function getContentTitle() : string
    {
        return tr("Вход клиенти");
    }
}
