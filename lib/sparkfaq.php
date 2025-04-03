<?php
include_once("class/pages/StorePage.php");
include_once("beans/FAQItemsBean.php");
include_once("beans/FAQSectionsBean.php");

$page = new StorePage();
$page->setTitle(tr("Frequently Asked Questions"));

$sections = new FAQSectionsBean();
$itemID = -1;
$secID = -1;
if (isset($_GET["secID"])) {
    $secID = (int)$_GET["secID"];
}

$page->startRender();


$query = $sections->queryFull();
$num = $query->exec();

echo "<div class='column side faq_sections'>";
    echo "<div class='Caption'>".tr("Section")."</div>";
    echo "<div class='menu_links'>";
    while ($result = $query->nextResult()) {
        $itemID = $result->get($sections->key());
        $selected = "";
        if ($itemID == $secID) $selected = "selected";

        echo "<a class='item $selected' href='?secID=$itemID'>" . $result->get("section_name") . "</a>";

    }
    echo "</div>"; //menu_links

echo "</div>"; //column

echo "<div class='column qa'>";
echo "<div class='Caption'>".tr("Frequently Asked Questions")."</div>";
if ($secID<1 && $itemID>0) $secID=$itemID;
if ($secID>0) {
    $faq = new FAQItemsBean();
    $query = $faq->queryFull();
    $query->select->where()->add($sections->key(), $secID);
    $num = $query->exec();
    if ($num>0) {
        while ($result = $query->nextResult()) {
            echo "<div class='question'><div class='icon'></div>" . $result->get("question") . "</div>";
            echo "<div class = 'answer'>";
            echo $result->get("answer");
            echo "</div>";
        }
    }
}
echo "</div>"; //page_data


$page->finishRender();
?>
