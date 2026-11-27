<?php
include_once("beans/ConfigBean.php");

/**
 * The product page settings of the catalog - checkboxes of Content > Config (section store_config, the group
 * 'Product Page'): the product query, in-stock notice and phone order buttons (on until saved off) and the stock
 * amount (off). The order buttons (Fast Order, Buy) come with the store features (DetailsSidePane).
 */
class ProductPageSettings
{
    const string SECTION = "store_config";

    const string BUTTON_QUERY = "product_button_query";
    const string BUTTON_NOTIFY = "product_button_notify";
    const string BUTTON_PHONE = "product_button_phone";
    const string SHOW_STOCK = "product_show_stock";

    /**
     * The default of each setting (a setting never saved) - SparkStoreConfigForm shows the same
     * @var array<string, bool>
     */
    const array DEFAULTS = array(
        ProductPageSettings::BUTTON_QUERY => true,
        ProductPageSettings::BUTTON_NOTIFY => true,
        ProductPageSettings::BUTTON_PHONE => true,
        ProductPageSettings::SHOW_STOCK => false,
    );

    /**
     * The setting '$key' - its default when never saved
     */
    public static function Get(string $key) : bool
    {
        $default = ProductPageSettings::DEFAULTS[$key] ?? false;
        $config = ConfigBean::Factory();
        $config->setSection(ProductPageSettings::SECTION);
        $value = $config->get($key, null);
        if (is_null($value)) return $default;
        return (bool)(int)$value;
    }
}
