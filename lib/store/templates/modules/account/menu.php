<?php
//
// account module - store menu. Site menu.php runs after it and extends AccountTemplatePage::Menu().
// Items: name, module path.
//
$menu = AccountTemplatePage::Menu();

$menu->append(new MenuItem(tr("История на поръчките"), "orders"));
