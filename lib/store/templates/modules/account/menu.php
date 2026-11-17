<?php
//
// account module - store menu. Site menu.php runs after it and extends AccountContent::Menu().
// Items: name, module path.
//
$menu = AccountContent::Menu();

$menu->append(new MenuItem(tr("История на поръчките"), "orders"));
$menu->append(new MenuItem(tr("Регистриран адрес"), "address"));
$menu->append(new MenuItem(tr("Детайли за фактуриране"), "invoice"));
$menu->append(new MenuItem(tr("Редакция на профил"), "profile"));
