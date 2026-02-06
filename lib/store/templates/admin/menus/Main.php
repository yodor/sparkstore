<?php
$menu = array();

$menu[] = new MenuItem("Магазин", Spark::Get(Config::ADMIN_LOCAL) . "/store/index.php", "class:icon_store");

$menu[] = new MenuItem("Поръчки", Spark::Get(Config::ADMIN_LOCAL) . "/orders/index.php", "class:icon_orders");

$menu[] = new MenuItem("Клиенти", Spark::Get(Config::ADMIN_LOCAL) . "/clients/index.php", "class:icon_clients");

$menu[] = new MenuItem("Съдържание", Spark::Get(Config::ADMIN_LOCAL) . "/content/index.php", "class:icon_content");

$menu[] = new MenuItem("Настройки", Spark::Get(Config::ADMIN_LOCAL) . "/settings/index.php", "class:icon_settings");

$menu[] = new MenuItem("Контакти", Spark::Get(Config::ADMIN_LOCAL) . "/contact_requests/list.php", "class:icon_settings");

?>