<?php
//the home of a store site (StoreHome) - a site sets its own content in its path file main/path/home.php
$config = TemplateConfig::Factory();
$config->contentClass = "StoreHome";
$config->requireAuth = false;
