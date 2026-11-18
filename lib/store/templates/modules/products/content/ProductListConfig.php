<?php
include_once("templates/TemplateConfig.php");

/**
 * The settings of a products list (ProductListContent::Configure()): which list the path shows
 */
class ProductListConfig extends TemplateConfig
{
    /**
     * All products and their categories - /products/, /products/category/<catID>/<slug>/
     */
    const string LIST_ALL = "";

    /**
     * Products on promotion - /products/promos/, /products/promos/<catID>/<slug>/
     */
    const string LIST_PROMOS = "promos";

    /**
     * The keyword search (not indexed) - /products/search/, /products/search/<catID>/<slug>/
     */
    const string LIST_SEARCH = "search";

    /**
     * One of the LIST_ constants
     * @var string
     */
    public string $list = ProductListConfig::LIST_ALL;
}
