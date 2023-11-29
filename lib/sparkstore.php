<?php

$globals = SparkGlobals::Instance();

$globals->addIncludeLocation("store/beans/");
$globals->addIncludeLocation("store/auth/");

$location = $globals->get("LOCAL");

//sparkbox frontend classes location (js/css/images) - HTTP accessible - without ending slash
$globals->set("STORE_LOCAL", $location . "/storefront");
$globals->set("LOGO_NAME", "logo_header.svg");
$globals->set("LOGO_PATH", $globals->get("INSTALL_PATH")."/storefront/images/");

//setup order recipient email address
include_once("beans/ConfigBean.php");
$config = ConfigBean::Factory();
$config->setSection("store_config");

//override ORDER_ADMIN_EMAIL
$order_email = $config->get("email_orders", "");
if (strlen(trim($order_email))<1) {
    if (defined("ORDER_ADMIN_EMAIL")) {
        $order_email = ORDER_ADMIN_EMAIL;
    }
}
$globals->set("ORDER_EMAIL", $order_email);

$globals->export();

?>
