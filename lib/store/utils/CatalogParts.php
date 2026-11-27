<?php

/**
 * The optional parts of the product data of the store catalog. Every store site has the products, their categories,
 * photos, prices, stock and promotions; these parts a site may not use - all on by default, a site switches off the
 * ones it does not use in its site init (class/templates/init.php):
 *
 *     CatalogParts::Disable(CatalogParts::BRANDS, CatalogParts::VARIANTS);
 *
 * A disabled part is left out of the admin as a whole - its admin pages answer 404 and leave the menu, the products
 * list has no columns, filters, search columns and row actions of it, the product editor no inputs of it:
 *
 *  - brands:   Store > Brands; the Brand column, filter and search; the brand input of the editor
 *  - sections: Store > Sections (and their banners); the Sections column, filter, search and row action; the
 *              section checkboxes of the editor
 *  - classes:  Store > Classes and Attributes; the Class and Attributes columns, the class filter and search; the
 *              class and its attribute values in the editor
 *  - variants: Store > Options; the products' Options and Variants (and the variant gallery); the Variants column
 *
 * The storefront follows the data (a product without variants shows no variant choice), so it needs no switch.
 * A part is about how a site is built, not its content - set in code, not in the admin. Features (Features) are
 * a different thing: what a site does (shop, fast_order) - modules and routes of their own.
 */
class CatalogParts
{
    const string BRANDS = "brands";
    const string SECTIONS = "sections";
    const string CLASSES = "classes";
    const string VARIANTS = "variants";

    /**
     * The admin paths of each part (a path and everything below it)
     */
    const array ADMIN_PATHS = array(
        CatalogParts::BRANDS => array("/store/brands"),
        CatalogParts::SECTIONS => array("/store/sections"),
        CatalogParts::CLASSES => array("/store/classes", "/store/attributes"),
        CatalogParts::VARIANTS => array("/store/options", "/store/products/variants"),
    );

    /**
     * @var array<string, bool> the disabled parts
     */
    protected static array $disabled = array();

    /**
     * Switch parts off (site init)
     * @param string ...$parts
     * @return void
     * @throws Exception an unknown part
     */
    public static function Disable(string ...$parts) : void
    {
        foreach ($parts as $part) {
            if (!isset(CatalogParts::ADMIN_PATHS[$part])) throw new Exception("Unknown catalog part '$part'");
            CatalogParts::$disabled[$part] = true;
        }
    }

    public static function Enabled(string $part) : bool
    {
        return !isset(CatalogParts::$disabled[$part]);
    }

    /**
     * @return string[] the disabled parts
     */
    public static function Disabled() : array
    {
        return array_keys(CatalogParts::$disabled);
    }

    /**
     * @return string[] the admin paths of the disabled parts (the store admin init closes them)
     */
    public static function DisabledPaths() : array
    {
        $paths = array();
        foreach (CatalogParts::Disabled() as $part) {
            foreach (CatalogParts::ADMIN_PATHS[$part] as $path) $paths[] = $path;
        }
        return $paths;
    }
}
