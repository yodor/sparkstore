<?php

$globals = SparkGlobals::Instance();

$globals->addIncludeLocation("store/beans/");
$globals->addIncludeLocation("store/auth/");

$location = $globals->get("LOCAL");

//sparkbox frontend classes location (js/css/images) - HTTP accessible - without ending slash
$globals->set("STORE_LOCAL", $location . "/storefront");

$globals->export();

?>