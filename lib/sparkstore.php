<?php

$globals = SparkGlobals::Instance();

$globals->addIncludeLocation("store/beans/");
$globals->addIncludeLocation("store/auth/");

$location = $globals->get("LOCAL");

//sparkbox frontend classes location (js/css/images) - HTTP accessible - without ending slash
$globals->set("STORE_LOCAL", $location . "/storefront");
$globals->set("LOGO_NAME", "logo_header.svg");
$globals->set("LOGO_PATH", $globals->get("INSTALL_PATH")."/storefront/images/");

$globals->export();

function isProductInCategories($categories_id=array(), $prod_categories_id=array()) {
    if ($categories_id[0] != ""){
        if ($prod_categories_id[0] != 0){
            foreach ($prod_categories_id as $prod_category_id){
                if (in_array($prod_category_id,$categories_id)){
                    return true;
                }
            }
        }
    }
    return false;
}
?>