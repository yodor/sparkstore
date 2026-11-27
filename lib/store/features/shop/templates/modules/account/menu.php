<?php
//
// account module - store menu. Site menu.php runs after it and extends AccountContent::Menu().
// Items: name, module path.
//
include_once("store/features/shop/templates/modules/account/content/AccountContent.php");
include_once("utils/menu/MenuItem.php");
$menu = AccountContent::Menu();

$menu->append(new MenuItem(tr("Order history"), "orders"));
$menu->append(new MenuItem(tr("Registered address"), "address"));
$menu->append(new MenuItem(tr("Invoice details"), "invoice"));
$menu->append(new MenuItem(tr("Edit profile"), "profile"));
