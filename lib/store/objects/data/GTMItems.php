<?php
include_once("objects/data/GTMCommand.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/cart/Cart.php");

/**
 * GA4 e-commerce for the store: one item shape for every event (view_item, add_to_cart, begin_checkout, purchase,
 * generate_lead). An event of a POST that redirects (the cart add) goes through the core GTMQueue.
 */
class GTMItems
{
    /**
     * The GA4 item of a product
     */
    public static function Item(SellableItem $sellable, int $index = 0, int $quantity = 1, ?float $price = null) : array
    {
        $item = array(
            "item_id" => (string)$sellable->getProductID(),
            "item_name" => $sellable->getTitle(),
            "affiliation" => (string)Spark::Get(Config::SITE_DOMAIN),
            "index" => $index,
            "price" => round($price ?? $sellable->getPriceInfo()->getSellPrice(), 2),
            "quantity" => $quantity,
        );
        if ($sellable->getBrandName()) $item["item_brand"] = $sellable->getBrandName();
        if ($sellable->getCategoryName()) $item["item_category"] = $sellable->getCategoryName();
        return $item;
    }

    /**
     * An event with the store currency, its value and its items
     */
    public static function Event(string $name, array $items, float $value, array $parameters = array()) : GTMCommand
    {
        $event = new GTMCommand(GTMCommand::COMMAND_EVENT);
        $event->setType($name);
        $event->addParameter("currency", (string)Spark::Get(StoreConfig::DEFAULT_CURRENCY));
        $event->addParameter("value", (string)round($value, 2));
        foreach ($parameters as $key => $parameter) $event->addParameter($key, $parameter);
        $event->addParameter("items", $items);
        return $event;
    }

    /**
     * An event of the cart: its items and its total
     */
    public static function CartEvent(string $name, Cart $cart, array $parameters = array()) : GTMCommand
    {
        $items = array();
        $index = 0;
        foreach ($cart->items() as $entry) {
            if (!($entry instanceof CartEntry)) continue;
            $items[] = GTMItems::Item($entry->getItem(), $index++, $entry->getQuantity(), $entry->getPrice());
        }
        return GTMItems::Event($name, $items, $cart->getItemsTotal(), $parameters);
    }
}
