<?php
//
// admin module - store menu, included after the core menu.php: store sections first, then the core
// menu (Content, Settings), contacts last
//
include_once("objects/events/TemplateEvent.php");
include_once("utils/menu/MenuItem.php");

$menu = SparkTemplateAdminPage::Menu() ?? new MenuItemList();

$store = new MenuItem("Store", "store");
$store->append(new MenuItem("Sections", "sections"));
$store->append(new MenuItem("Brands", "brands"));
$store->append(new MenuItem("Categories", "categories"));
$store->append(new MenuItem("Classes", "classes"));
$store->append(new MenuItem("Attributes", "attributes"));
$store->append(new MenuItem("Options", "options"));
$store->append(new MenuItem("Products", "products"));
$store->append(new MenuItem("Promotions", "promos"));

$orders = new MenuItem("Orders", "orders");
$clients = new MenuItem("Clients", "clients");

$menu->prepend($clients);
$menu->prepend($orders);
$menu->prepend($store);

//store configuration under Content
foreach ($menu->toArray() as $item) {
    if ($item instanceof MenuItem && strcmp($item->getHref(), "content") === 0) {
        $config = new MenuItem("Config", "config");
        $config->append(new MenuItem("Base", "base", "list"));
        $config->append(new MenuItem("Marketing", "marketing", "list"));
        $item->prepend($config);
    }
}

$contacts = new MenuItem("Contacts", "contacts");
$contacts->append(new MenuItem("Addresses", "addresses", "code-class.png"));
$menu->append($contacts);

SparkTemplateAdminPage::SetMenu($menu);
