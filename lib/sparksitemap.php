<?php
include_once("session.php");
include_once("store/beans/SellableProducts.php");
include_once("store/beans/ProductCategoriesBean.php");




$bean = new SellableProducts();
$qry = $bean->query();
$qry->select->fields()->reset();
$qry->select->fields()->set("prodID", "update_date");
$qry->select->fields()->setExpression("(SELECT GROUP_CONCAT(pp.ppID SEPARATOR ';') FROM product_photos pp WHERE pp.prodID=sellable_products.prodID ORDER BY pp.position ASC)", "photos");
$qry->select->group_by = " prodID ";

$num = $qry->exec();

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:image='http://www.google.com/schemas/sitemap-image/1.1'>";
renderItem(fullURL(LOCAL."/home.php"));
renderItem(fullURL(LOCAL."/products/list.php"));
renderItem(fullURL(LOCAL."/products/promo.php"));
renderItem(fullURL(LOCAL."/contacts.php"));

//each product
while ($result = $qry->nextResult()) {
    $prodID = $result->get("prodID");

    $update_date = new DateTime($result->get("update_date"));

    renderItem(fullURL(LOCAL."/products/details.php?prodID=$prodID"), $update_date->format('Y-m-d'), $result->get("photos"));
}

//each category
$select = new SQLSelect();
$select->fields()->set("pc.catID");
$select->fields()->setExpression("(SELECT group_concat(sp.ppID ORDER BY sp.prodID DESC SEPARATOR ';' LIMIT 6) FROM sellable_products sp WHERE sp.catID = pc.catID)", "product_photos");
$select->from = " product_categories pc ";

$query = new SQLQuery($select);
$num = $query->exec();
while ($result = $query->nextResult())
{
    $catID = $result->get("catID");
    $photos = (string)$result->get("product_photos");
    if (strlen($photos)>0) {
        renderItem(fullURL(LOCAL . "/products/list.php?catID=$catID"), "", $photos);
    }
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
                echo "<image:loc>".fullURL(LOCAL."/storage.php?cmd=image&amp;width=640&amp;height=-1&amp;class=ProductPhotosBean&amp;id=".$ppID)."</image:loc>";
            echo "</image:image>";
        }
    }
    echo "</url>";

}
?>