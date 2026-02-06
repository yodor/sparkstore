<?php
class StoreConfig {
    /**
     * SparkStore frontend classes location (js/css/images) - HTTP accessible - without ending slash
     */
    const string STORE_LOCAL = "STORE_LOCAL";
    const string LOGO_NAME  = "LOGO_NAME";
    const string LOGO_PATH = "LOGO_PATH";

    /**
     * Admin email for receiving customer orders after checkout or fast orders.
     * Default value equals DEFAULT_SERVICE_EMAIL, local configuration can can overwrite by
     * setting ORDER_EMAIL in config/defaults.php or set config value in DB
     * ConfigBean -> section 'store_config' -> value 'order_email'
     */
    const string ORDER_EMAIL = "ORDER_EMAIL";
    /**
     * Admin email for receiving errors during ordering/checkout
     */
    const string ORDER_ERROR_EMAIL = "ORDER_ADMIN_EMAIL";

    /**
     * ISO3 store currency - Default is EUR
     */
    const string DEFAULT_CURRENCY = "DEFAULT_CURRENCY";
    /**
     * Currency symbol - Default is &euro;
     */
    const string DEFAULT_CURRENCY_SYMBOL = "DEFAULT_CURRENCY_SYMBOL";

    const string PRODUCT_ITEM_SLUG = "PRODUCT_ITEM_SLUG";
    const string CATEGORY_ITEM_SLUG = "CATEGORY_ITEM_SLUG";


    /**
     * Show double prices - convert from default currency to DOUBLE_PRICE_CURRENCY using DOUBLE_PRICE_RATE
     */
    const string DOUBLE_PRICE_ENABLED = "DOUBLE_PRICE_ENABLED";
    /**
     * ISO3 Currency name
     */
    const string DOUBLE_PRICE_CURRENCY = "DOUBLE_PRICE_CURRENCY";
    /**
     *  Currency symbol
     */
    const string DOUBLE_PRICE_SYMBOL = "DOUBLE_PRICE_SYMBOL";
    /**
     *  Currency rate to the main currency as 1/rate
     */
    const string DOUBLE_PRICE_RATE = "DOUBLE_PRICE_RATE";

}
?>