<?php
include_once("session.php");
include_once("store/beans/SellableProducts.php");
include_once("utils/menu/BeanMenuFactory.php");
include_once("beans/MenuItemsBean.php");
include_once("storage/StorageItem.php");

$bean = new SellableProducts();
$qry = $bean->query();
$qry->select->fields()->reset();
$qry->select->fields()->set("prodID", "product_name", "update_date");
$qry->select->fields()->setExpression("(SELECT GROUP_CONCAT(pp.ppID SEPARATOR ';') FROM product_photos pp WHERE pp.prodID=sellable_products.prodID ORDER BY pp.position ASC)", "photos");
$qry->select->group_by = " prodID ";

$num = $qry->exec();

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:image='http://www.google.com/schemas/sitemap-image/1.1'>";

$factory = new BeanMenuFactory(new MenuItemsBean());
$list = $factory->menu();
if ($list instanceof MenuItemList) {
    $itr = $list->iterator();
    while($menuItem = $itr->next()) {
        if ($menuItem instanceof MenuItem) {
            renderItem(fullURL($menuItem->getHref()));
        }
    }
}

//each product
while ($result = $qry->nextResult()) {
    $prodID = $result->get("prodID");
    $productName = $result->get("product_name");

    $update_date = new DateTime($result->get("update_date"));
    $photos = (string)$result->get("photos");
    if (strlen($photos)>0) {
        $productURL = LOCAL . "/products/details.php?prodID=$prodID";
        if (PRODUCT_ITEM_SLUG) {
            $productURL = LOCAL."/products/".$prodID."/".$productName;
        }
        renderItem(fullURL($productURL), $update_date->format('Y-m-d'), $photos);
    }
}

//each category
$select = new SQLSelect();
$select->fields()->set("pc.catID, pc.category_name");
$select->fields()->setExpression("(SELECT group_concat(sp.ppID ORDER BY sp.prodID DESC SEPARATOR ';' LIMIT 6) FROM sellable_products sp WHERE sp.catID = pc.catID)", "product_photos");
$select->from = " product_categories pc ";
//echo $select->getSQL();
$query = new SQLQuery($select);
$num = $query->exec();
while ($result = $query->nextResult())
{
    $catID = $result->get("catID");
    $categoryName = $result->get("category_name");
    $photos = (string)$result->get("product_photos");
    $categoryURL = LOCAL . "/products/list.php?catID=$catID";
    if (CATEGORY_ITEM_SLUG) {
        $categoryURL = LOCAL . "/products/category/".$catID."/".$categoryName;
    }
    renderItem(fullURL($categoryURL), "", $photos);

}

if (isset($items_add) && is_array($items_add)) {
    foreach ($items_add as $idx=>$item) {
        renderItem(fullURL($item));
    }
}

echo "</urlset>";

function renderItem(string $loc, string $lastmod="", string $photos="")
{
    //2018-06-04
    echo "<url>";
    echo "<loc>$loc</loc>";
    if ($lastmod) {
        echo "<lastmod>$lastmod</lastmod>";
    }
    if ($photos) {
        $photos = explode(";", $photos);
        foreach ($photos as $idx=>$ppID) {
            echo "<image:image>";
                $imageLocation = StorageItem::Image($ppID,"ProductPhotosBean", 0,0);
                echo "<image:loc>".fullURL($imageLocation)."</image:loc>";
            echo "</image:image>";
        }
    }
    echo "</url>";

}
?>