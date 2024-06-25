<?php
include_once("session.php");

include_once("class/pages/StorePage.php");

include_once("beans/NewsItemsBean.php");
include_once("components/PublicationsComponent.php");

$page = new StorePage();
$page->addCSS(STORE_LOCAL . "/css/news.css");

$bean = new NewsItemsBean();

$pac = new PublicationsComponent($bean, LOCAL . "/news/index.php");

$pac->processInput();


$page->startRender();

echo "<div class='news_view'>";

echo "<div class='column other'>";

$pac->render();

echo "</div>"; //column_other

echo "<div class='column main'>";

$arr = $pac->getSelection();
if (count($arr)>0) {
    $qry = $pac->getBean()->query(...$pac->getSelectionColumns());
    $qry->select->where()->add($bean->key(), "(" . implode(",", $arr) . ")", " IN ");
    $qry->exec();

    while ($item = $qry->next()) {
        echo "<div class='item'>";
        echo "<div class='title'>" . $item["item_title"] . "</div>";
        echo "<div class='date'>" . date($pac->getItemRenderer()->getDateFormat(), strtotime($item["item_date"])) . "</div>";

        echo "<div class='image'>";
        $img_href = StorageItem::Image($item[$qry->key()], $bean);
        echo "<img width=100% src='$img_href'>";
        echo "</div>";

        echo "<div class='contents'>" . $item["content"] . "</div>";
        echo "</div>"; //item
    }
}

echo "</div>"; //column_main



echo "</div>";//news_view


$page->finishRender();
?>
