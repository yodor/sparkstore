<?php
include_once("beans/DBTableBean.php");

class ProductsTemplateBean extends DBTableBean
{
    public function __construct()
    {
        parent::__construct("products_template");
    }
}
?>