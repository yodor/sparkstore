<?php
include_once("utils/menu/MenuItemList.php");
include_once("store/utils/url/CategoryURL.php");
include_once("store/utils/url/ProductListURL.php");
include_once("store/beans/ProductCategoriesBean.php");

/**
 * Puts the current addresses of the products module in the menu links entered in the admin:
 *  - /products/list.php?catID=110, /products/?catID=110, /products/category/110/<any slug>/ →
 *    /products/category/110/<category name slug>/ (other parameters stay)
 *  - /products/list.php → /products/
 *
 * The menu then links to the addresses the pages answer at (no 301 for the visitors and the crawlers) and the
 * selection of the menu compares the same addresses. The category names come with one query.
 * Links of unknown categories stay as they are.
 */
class StoreMenuLinks
{
    public static function Resolve(MenuItemList $menu) : void
    {
        $local = Spark::Get(Config::LOCAL);
        $products = $local . "/products/";

        //item => [catID, other parameters] of the category links; list.php links are set right away
        $links = array();
        $menu->forEachItem(function (MenuItem $item) use ($local, $products, &$links) {
            $href = trim($item->getHref());
            $parts = parse_url($href);
            if ($parts === false || isset($parts["host"]) || isset($parts["scheme"])) return;

            $path = $parts["path"] ?? "";
            if (!str_starts_with($path, $products)) return;
            $rest = substr($path, strlen($products));

            $parameters = array();
            if (isset($parts["query"])) {
                parse_str($parts["query"], $parameters);
            }

            $catID = 0;
            if (in_array($rest, array("", "list.php", "index.php"), true)) {
                $catID = (int)($parameters["catID"] ?? 0);
                unset($parameters["catID"]);
                if ($catID < 1) {
                    if ($rest !== "") {
                        $item->setHref(StoreMenuLinks::Address($products, $parameters));
                    }
                    return;
                }
            }
            else if (preg_match("#^category/([0-9]+)(/.*)?$#", $rest, $match)) {
                $catID = (int)$match[1];
            }
            else {
                return;
            }

            $links[] = array($item, $catID, $parameters);
        });

        if (count($links) == 0) return;

        $ids = array_unique(array_map(fn(array $link) => $link[1], $links));
        $names = array();
        $categories = new ProductCategoriesBean();
        $query = $categories->query($categories->key(), "category_name");
        $query->stmt->where()->expression($categories->key() . " IN (" . implode(",", $ids) . ")");
        $query->exec();
        while ($result = $query->next()) {
            $names[(int)$result[$categories->key()]] = (string)$result["category_name"];
        }

        foreach ($links as [$item, $catID, $parameters]) {
            if (!isset($names[$catID])) continue;
            $url = new CategoryURL();
            $url->setData(array("catID" => $catID, "category_name" => $names[$catID]));
            $item->setHref(StoreMenuLinks::Address($url->toString(), $parameters));
        }
    }

    protected static function Address(string $address, array $parameters) : string
    {
        if (count($parameters) == 0) return $address;
        return $address . (str_contains($address, "?") ? "&" : "?") . http_build_query($parameters);
    }
}
