<?php
//
// account module - store menu. Site menu.php runs after it and extends AccountContent::Menu().
// Items: name, module path.
//
include_once("store/templates/modules/account/content/AccountContent.php");
include_once("utils/menu/MenuItem.php");
$menu = AccountContent::Menu();

$menu->append(new MenuItem(tr("История на поръчките"), "orders"));
$menu->append(new MenuItem(tr("Регистриран адрес"), "address"));
$menu->append(new MenuItem(tr("Детайли за фактуриране"), "invoice"));
$menu->append(new MenuItem(tr("Редакция на профил"), "profile"));
