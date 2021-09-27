<?php

interface ICartListener {

    const ITEM_ADD = "item_added";
    const ITEM_REMOVE = "item_removed";
    const ITEM_QTY_INCREMENT = "item_quantity_increment";
    const ITEM_QTY_DECREMENT = "item_quantity_decrement";
    const CART_CLEAR = "cart_clear";

    /**
     * Called before the actual operation take place
     * @param string $operation
     * @param ?CartItem $item
     * @param Cart $cart
     * @return mixed
     */
    public function before(string $operation, ?CartItem $item, Cart $cart);

    /**
     * Called after the actial operation take place
     * @param string $operation
     * @param ?CartItem $item
     * @param Cart $cart
     * @return mixed
     */
    public function after(string $operation, ?CartItem $item, Cart $cart);

}
?>