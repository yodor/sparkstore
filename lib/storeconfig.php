<?php
class StoreConfig {
    /**
     * SparkStore frontend classes location (js/css/images) - HTTP accessible - without ending slash
     * Default to LOCAL/storefront
     * Static(string)
     */
    const string STORE_LOCAL = "STORE_LOCAL";

    /**
     * Header logo filename
     * Default(string): logo_header.svg
     */
    const string LOGO_NAME  = "LOGO_NAME";

    /**
     * Header logo server-side path
     * Default(string): <sparkstore>/storefront/images/
     */
    const string LOGO_PATH = "LOGO_PATH";

    /**
     * The shop's e-mail: receives orders (checkout, fast orders, vouchers) and the contact and product enquiries.
     * Set in config/store.php, the admin value (Content → Config → Base, 'email_orders') wins when not empty.
     * Default(string): DEFAULT_SERVICE_EMAIL
     */
    const string ORDER_EMAIL = "ORDER_EMAIL";

    /**
     * E-mail for receiving errors during ordering/checkout (OrderErrorAdminMailer) - set in config/store.php.
     * Default(string): empty - the error goes to ORDER_EMAIL
     */
    const string ORDER_ERROR_EMAIL = "ORDER_ERROR_EMAIL";

    /**
     * ISO3 store currency
     * Default(string): EUR
     */
    const string DEFAULT_CURRENCY = "DEFAULT_CURRENCY";
    /**
     * Currency symbol
     * Default(string): &euro;
     */
    const string DEFAULT_CURRENCY_SYMBOL = "DEFAULT_CURRENCY_SYMBOL";

}