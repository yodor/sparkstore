<?php
Module::Active()->getAuthenticator()?->logout();

header("Location: " . Spark::Get(Config::LOCAL) . "/");
exit;
