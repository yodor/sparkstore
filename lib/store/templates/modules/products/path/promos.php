<?php
//the products on promotion: /products/promos/[<catID>/<slug>/]
include_once("store/templates/modules/products/content/ProductListContent.php");
include_once("store/templates/modules/products/content/ProductListConfig.php");
$config = ProductListContent::Configure(ProductListConfig::LIST_PROMOS);
