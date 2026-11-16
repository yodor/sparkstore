<?php
//products module entry (the catalog) - link this folder into the site document root as 'products'
include_once("templates/Template.php");
include_once("templates/Module.php");

Module::Run("products");
