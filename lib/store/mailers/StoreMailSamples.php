<?php
include_once("store/beans/OrdersBean.php");
include_once("store/beans/SellableProducts.php");

/**
 * The sample values of the store mail previews (<layer>/mails/previews.php of the store and its features)
 */
class StoreMailSamples
{
    /**
     * The ID of the last order
     * @throws Exception there are no orders
     */
    public static function LastOrder() : int
    {
        $orders = new OrdersBean();
        $query = $orders->query($orders->key());
        $query->stmt->order($orders->key(), OrderDirection::DESC);
        $query->stmt->limit(1);
        $query->exec();
        $row = $query->next();
        $query->free();
        if (!is_array($row)) throw new Exception(tr("There are no orders yet"));
        return (int)$row[$orders->key()];
    }

    /**
     * The ID of the first visible product
     * @throws Exception there are no products
     */
    public static function FirstProduct() : int
    {
        $products = new SellableProducts();
        $query = $products->query("prodID");
        $query->stmt->order("prodID", OrderDirection::ASC);
        $query->stmt->limit(1);
        $query->exec();
        $row = $query->next();
        $query->free();
        if (!is_array($row)) throw new Exception(tr("There are no products yet"));
        return (int)$row["prodID"];
    }
}
