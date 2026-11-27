<?php
include_once("storeconfig.php");

$location = Spark::Get(Config::LOCAL);

Spark::Set(StoreConfig::STORE_LOCAL, $location . "/storefront");
Spark::Set(StoreConfig::LOGO_NAME, "logo_header.svg");
//the store default (sparkstore/storefront/images) - INSTALL_PATH was never set
Spark::Set(StoreConfig::LOGO_PATH, dirname(__DIR__) . "/storefront/images/");

//the image sizes of the store (the product list items, the product page) next to the core ones - a site disables
//or adds sizes in config/settings.php (a disabled size stays disabled) or config/app.php
include_once("store/utils/StoreImageSize.php");
ImageSizes::Enable(...StoreImageSize::cases());

Spark::Set(StoreConfig::DEFAULT_CURRENCY, "EUR");
Spark::Set(StoreConfig::DEFAULT_CURRENCY_SYMBOL, "&euro;");

//the features of the store - a site enables the ones it has in config/include.php: Features::Enable("shop", "fast_order")
include_once("features/Features.php");
Features::Register(__DIR__ . "/store/features/shop");
Features::Register(__DIR__ . "/store/features/fast_order");

//slugified URLs
Spark::Set(Config::STORAGE_ITEM_SLUG, TRUE);

Spark::Set(StoreConfig::ORDER_EMAIL, Spark::Get(Config::DEFAULT_SERVICE_EMAIL));


Spark::Set("UNICREDIT_KEY_FILE", Spark::Get("CACHE_PATH")."/../certs/avalon_private_key.pem");
Spark::Set("UNICREDIT_CERT_FILE", Spark::Get("CACHE_PATH")."/../certs/avalon_cert.pem");
//Spark::Set("TBI_UID", "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");



//read local store settings and override ie ORDER_EMAIL, DEFAULT_CURRENCY etc
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