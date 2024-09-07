<?php
include_once("session.php");
include_once("class/pages/StorePage.php");
include_once("beans/DynamicPagePhotosBean.php");
// include_once("beans/DynamicPagesBean.php");
include_once("beans/MenuItemsBean.php");

$page = new StorePage();

if (!isset($_GET["page_id"]) || !isset($_GET["page_class"])) {

    exit;
}

$page_class = DBConnections::Get()->escape($_GET["page_class"]);
$page_id = (int)$_GET["page_id"];

try {
    @include_once("class/beans/$page_class.php");
    @include_once("beans/$page_class.php");
    $b = new $page_class;

    $prkey = $b->key();
    $rrow = array();
    $qry = $b->queryField($prkey, $page_id, 1);
    $qry->select->fields()->set(" item_title, content, visible ");
    $num = $qry->exec();
    if ($num < 1) throw new Exception("This page is not available.");
    $rrow = $qry->next();
    if (!$rrow["visible"]) throw new Exception("This page is currently unavailable.");

}
catch (Exception $e) {
    Session::SetAlert($e->getMessage());
    header("Location: " . LOCAL . "/home.php");
    exit;
}

$menu1 = new MainMenu();
$menu1->setBean(new MenuItemsBean());
$menu1->construct();

$menu_bar1 = new MenuBarComponent($menu1);
$menu_bar1->setName("MenuItemsBean");

$page->startRender();

echo "<div class='$page_class'>";

echo "<div class='MenuBarWrapper'>";
$menu_bar1->render();
echo "</div>";

echo "<div class='photo'>";
$photo_href = StorageItem::Image($page_id, $page_class);

echo "<img src='$photo_href'>";

echo "</div>";

echo "<div class='item_title'>" . $rrow["item_title"] . "</div>";

if (isset($rrow["subtitle"])) {
    echo "<div class='subtitle'>" . $rrow["subtitle"] . "</div>";
}

echo "<div class='content'>" . $rrow["content"] . "</div>";

echo "<div class='PagePhotos'>";

$dpp = new DynamicPagePhotosBean();
$qry = $dpp->queryField("dpID", $page_id, 1);
$qry->select->fields()->set("ppID", "caption");
$num_photos = $qry->exec();

$image_popup = new ImagePopup();
$image_popup->setBean($dpp);
$image_popup->setAttribute("rel", get_class($dpp));

if ($num_photos && $dpprow = $qry->next()) {
    $photo_id = $dpprow["ppID"];

    echo "<div class='photo_item' id='$photo_id' class='{get_class($dpp)}'>";

    $image_popup->setID($photo_id);
    $image_popup->render();

    echo "<h1 class='Caption'>";
    echo $dpprow["caption"];
    echo "</h1>";

    echo "</div>";

}

echo "</div>";

echo "</div>"; //$page_class

$page->finishRender();
?>