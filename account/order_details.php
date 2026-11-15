<?php
include_once("class/pages/AccountPage.php");
include_once("store/components/order/OrderView.php");
include_once("components/Action.php");

$page = new AccountPage();
$page->setTitle(tr("Детайли за поръчка"));
$page->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/print.css");

//only orders of the logged in client
$view = new OrderView();
if (!$view->setOrder((int)($_GET["orderID"] ?? -1), $page->getUserID())) {
    Session::SetAlert(tr("Няма достъп до тази поръчка"));
    header("Location: orders.php");
    exit;
}

$column = new Container(false);
$column->setComponentClass("column details");

$caption = new Component(false);
$caption->setTagName("h1");
$caption->setComponentClass("Caption");
$caption->setText($page->getTitle());
$column->items()->append($caption);

$panel = new Container(false);
$panel->setComponentClass("panel");
$column->items()->append($panel);

$back = new Action("back", "javascript:window.history.back();");
$back->setClassName("ColorButton");
$back->setContents(tr("<<"));
$panel->items()->append($back);

$print = new Action("print", "javascript:window.print();");
$print->setClassName("ColorButton");
$print->setContents(tr("Print"));
$panel->items()->append($print);

$panel->items()->append($view);

$page->startRender();
$column->render();
$page->finishRender();
