<?php
//account module entry - the site links this folder as its document root folder 'account'
include_once("templates/Template.php");
include_once("templates/Module.php");

Module::Run("account");
