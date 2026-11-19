<?php
//the home of a store site (StoreHome) - a site sets its own content in its path file main/path/home.php
include_once("store/templates/modules/main/content/StoreHome.php");
$config = StoreHome::Configure();
$config->requireAuth = false;
