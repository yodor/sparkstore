<?php

class TBIData
{
    public static $id;
    public static $name;
    public static $quantity;
    public static $price;
    public static $store_uid;
}

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