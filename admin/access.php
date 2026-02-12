<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");

$content = new AdminPage();

$content->startRender();

echo "Access to this resource is not allowed for your account.";

$content->finishRender();