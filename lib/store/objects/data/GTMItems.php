<?php
include_once("objects/data/GTMCommand.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/cart/Cart.php");
include_once("pages/HTMLHead.php");

/**
 * GA4 e-commerce for the store: one item shape for every event (view_item, add_to_cart, begin_checkout, purchase,
 * generate_lead), and the events queued for the next page - an event of a POST that redirects (the cart add) is
 * sent on the page after the redirect (StorePage::headFinalize), so the navigation does not lose it.
 */
class GTMItems
{
    const string SESSION_QUEUE = "gtm.queue";

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

    /**
     * Send '$event' on the next page
     */
    public static function Queue(GTMCommand $event) : void
    {
        $queue = Session::Get(GTMItems::SESSION_QUEUE, array());
        if (!is_array($queue)) $queue = array();
        //a few at most - a visitor who never reaches a page does not grow the session
        $queue = array_slice(array_merge($queue, array($event->jsonSerialize())), -5);
        Session::Set(GTMItems::SESSION_QUEUE, $queue);
    }

    /**
     * The queued events into '$head' - once (no session is started for it)
     */
    public static function Flush(HTMLHead $head) : void
    {
        if (!Session::Contains(GTMItems::SESSION_QUEUE)) return;
        $queue = Session::Get(GTMItems::SESSION_QUEUE, array());
        Session::Remove(GTMItems::SESSION_QUEUE);
        if (!is_array($queue)) return;
        foreach ($queue as $data) {
            if (!is_array($data) || empty($data["command"]) || empty($data["type"])) continue;
            $command = new GTMCommand((string)$data["command"]);
            $command->setType((string)$data["type"]);
            if (isset($data["parameters"]) && is_array($data["parameters"])) $command->setParameters($data["parameters"]);
            $head->addScript($command);
        }
    }
}
