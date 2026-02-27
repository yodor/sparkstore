<?php
include_once("objects/events/TemplateEvent.php");
include_once("utils/menu/MenuItem.php");

$menu = new MenuItemList();

$store = new MenuItem("Store", "store");
$menu->append($store);
//store
$item = new MenuItem("Attributes", "attributes");
$store->append($item);

$item = new MenuItem("Classes", "classes");
$store->append($item);

$item = new MenuItem("Options", "options");
$store->append($item);

$item = new MenuItem("Brands", "brands");
$store->append($item);

$item = new MenuItem("Sections", "sections");
$store->append($item);

$item = new MenuItem("Categories", "categories");
$store->append($item);

$item = new MenuItem("Products", "products");
$store->append($item);


$orders = new MenuItem("Orders", "orders");
$menu->append($orders);

$clients = new MenuItem("Clients", "clients");
$menu->append($clients);

$content = new MenuItem("Content", "content");
$menu->append($content);

$config = new MenuItem("Config", "config");
$content->append($config);

$baseConfig = new MenuItem("Базови", "base.php", "list");
$config->append($baseConfig);

$mrktConfig = new MenuItem("Маркетинг", "marketing.php", "list");
$config->append($mrktConfig);


$settings = new MenuItem("Settings", "settings");
$menu->append($settings);

$admins = new MenuItem("Admins", "admins");
$settings->append($admins);

$langs = new MenuItem("Languages", "languages");
$settings->append($langs);

$contacts = new MenuItem("Contacts", "contacts");
$menu->append($contacts);

$addresses = new MenuItem("Addresses", "addresses", "code-class.png");
$contacts->append($addresses);

SparkEventManager::emit(new TemplateEvent(TemplateEvent::MENU_CREATED, $menu));