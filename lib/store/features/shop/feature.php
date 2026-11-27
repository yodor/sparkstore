<?php
/**
 * The shop: the cart and the checkout, the client accounts (login, profile, orders), the orders and the clients in
 * the admin, the delivery settings and the courier offices, the order and account mails.
 * The data model (orders, cart, delivery, couriers) stays in the store library - the catalog reads it too.
 */
return new Feature(
    id: "shop",
    title: "Shop",
    layer: "store",
    routes: array("checkout", "account"),
    admin: array("orders", "clients"),
);
