<?php
//
// admin module - store menu, included after the core menu.php: store sections first, then the core
// menu (Content, Settings) - the features add theirs after it (shop: Orders, Clients); store configuration, photo gallery, logo and contact addresses under Content
//
include_once("objects/events/TemplateEvent.php");
include_once("utils/menu/MenuItem.php");
include_once("pages/SparkAdminPage.php");

$menu = SparkAdminPage::Menu() ?? new MenuItemList();

$store = new MenuItem("Store", "store");
$store->append(new MenuItem("Sections", "sections"));
$store->append(new MenuItem("Brands", "brands"));
$store->append(new MenuItem("Categories", "categories"));
$store->append(new MenuItem("Classes", "classes"));
$store->append(new MenuItem("Attributes", "attributes"));
$store->append(new MenuItem("Options", "options"));
$store->append(new MenuItem("Products", "products"));
$store->append(new MenuItem("Promotions", "promos"));

//Orders and Clients: the shop feature adds them after Store (features/shop/templates/modules/admin/menu.php)
$menu->prepend($store);

//store configuration under Content
foreach ($menu->toArray() as $item) {
    if ($item instanceof MenuItem && strcmp($item->getHref(), "content") === 0) {
        $config = new MenuItem("Config", "config");
        $config->append(new MenuItem("Base", "base", "list"));
        $item->prepend($config);
        $item->append(new MenuItem("Photo Gallery", "photogallery"));
        $item->append(new MenuItem("Logo", "logo"));
        //contacts page addresses (enquiries are mailed, not stored)
        $item->append(new MenuItem("Addresses", "addresses"));
    }
}

SparkAdminPage::SetMenu($menu);
