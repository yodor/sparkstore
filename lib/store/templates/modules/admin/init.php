<?php

$config = Module::Active();
$config->pageClass = AdminTemplatePageBase::class;

//reachable without login - every other path redirects to login before its path file runs
$config->addPublicPath("login", "password");
