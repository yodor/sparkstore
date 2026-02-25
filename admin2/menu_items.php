<?php
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

$settings = new MenuItem("Settings", "setting");
$menu->append($settings);

$contacts = new MenuItem("Contacts", "contacts");
$menu->append($contacts);

SparkEventManager::emit(new TemplateEvent(TemplateEvent::MENU_CREATED, $menu));