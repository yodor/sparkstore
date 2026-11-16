<?php
//the checkout needs a client: the account login, then back to confirm (AccountLogin uses 'login.redirect')
Session::Set("login.redirect", Module::PathURL("confirm")->toString());
Session::SetAlert(tr("Влезте в акаунта си или се регистрирайте."));
header("Location: " . Spark::Get(Config::LOCAL) . "/account/login");
exit;
