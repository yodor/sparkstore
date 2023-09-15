<?php
$menu = array();

$menu[] = new MenuItem("Магазин", ADMIN_LOCAL . "/store/index.php", "class:icon_store");

$menu[] = new MenuItem("Поръчки", ADMIN_LOCAL . "/orders/index.php", "class:icon_orders");

$menu[] = new MenuItem("Клиенти", ADMIN_LOCAL . "/clients/index.php", "class:icon_clients");

$menu[] = new MenuItem("Съдържание", ADMIN_LOCAL . "/content/index.php", "class:icon_content");

$menu[] = new MenuItem("Настройки", ADMIN_LOCAL . "/settings/index.php", "class:icon_settings");

$menu[] = new MenuItem("Контакти", ADMIN_LOCAL . "/contact_requests/list.php", "class:icon_settings");

?>