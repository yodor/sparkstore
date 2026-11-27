<?php
/**
 * The fast order: the 'Fast Order' button of the product page - a dialog for a name and a phone, mailed to the shop
 * (no order is stored). The checkout guest page offers it too when the shop is enabled.
 */
return new Feature(
    id: "fast_order",
    title: "Fast order",
    layer: "store",
);
