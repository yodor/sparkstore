<?php
//the keyword search (noindex): /products/search/[<catID>/<slug>/]
include_once("store/templates/modules/products/content/ProductListContent.php");
include_once("store/templates/modules/products/content/ProductListConfig.php");
$config = ProductListContent::Configure(ProductListConfig::LIST_SEARCH);
