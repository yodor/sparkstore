<?php
include_once("storeconfig.php");

Spark::EnableBeanLocation("store/beans/");
Spark::EnableBeanLocation("store/auth/");

$location = Spark::Get(Config::LOCAL);

Spark::Set(StoreConfig::STORE_LOCAL, $location . "/storefront");
Spark::Set(StoreConfig::LOGO_NAME, "logo_header.svg");
Spark::Set(StoreConfig::LOGO_PATH, Spark::Get("INSTALL_PATH")."/storefront/images/");

Spark::Set(StoreConfig::DEFAULT_CURRENCY, "EUR");
Spark::Set(StoreConfig::DEFAULT_CURRENCY_SYMBOL, "&euro;");

//show double prices - convert from default currency to EURO
Spark::Set(StoreConfig::DOUBLE_PRICE_ENABLED, false);
Spark::Set(StoreConfig::DOUBLE_PRICE_CURRENCY, "BGN");
Spark::Set(StoreConfig::DOUBLE_PRICE_SYMBOL, "лв.");
Spark::Set(StoreConfig::DOUBLE_PRICE_RATE, (1/1.95583));

//slugified URLs
Spark::Set(Config::STORAGE_ITEM_SLUG, TRUE);
Spark::Set(StoreConfig::CATEGORY_ITEM_SLUG, TRUE);
Spark::Set(StoreConfig::PRODUCT_ITEM_SLUG, TRUE);

Spark::Set(StoreConfig::ORDER_EMAIL, Spark::Get(Config::DEFAULT_SERVICE_EMAIL));


Spark::Set("UNICREDIT_KEY_FILE", Spark::Get("CACHE_PATH")."/../certs/avalon_private_key.pem");
Spark::Set("UNICREDIT_CERT_FILE", Spark::Get("CACHE_PATH")."/../certs/avalon_cert.pem");
//Spark::Set("TBI_UID", "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");

include_once("utils/TemplateFactory.php");
TemplateFactory::AddTemplateLocation("class/templates/admin");
TemplateFactory::AddTemplateLocation("store/templates/admin");


//read local store settings and override ie ORDER_EMAIL, DEFAULT_CURRENCY, PRODUCT_ITEM_SLUG etc
if (file_exists(APP_PATH."/config/store.php")) {
    include_once(APP_PATH."/config/store.php");
}

//allow ORDER_EMAIL override from DB configuration
if (!Spark::isStorageRequest()) {
    include_once("beans/ConfigBean.php");
    $config = ConfigBean::Factory();
    $config->setSection("store_config");

    $order_email = $config->get("email_orders");
    if (strlen(trim($order_email)) > 0) {
        Spark::Set(StoreConfig::ORDER_EMAIL, $order_email);
    }
}


function formatPrice($price, ?string $currency_symbol=null, bool $symbol_front=false) : string
{
    if (is_null($currency_symbol)) {
        $currency_symbol = Spark::Get(StoreConfig::DEFAULT_CURRENCY_SYMBOL);
    }
    $format = "%0.2f";

    if ($symbol_front) {
        $format = $currency_symbol." ".$format;
    }
    else {
        $format = $format." ".$currency_symbol;
    }

    return sprintf(trim($format), $price);
}