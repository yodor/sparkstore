<?php
include_once("store/templates/admin/ProductsListAddBase.php");
include_once("store/forms/ProductInputForm.php");
include_once("store/beans/ProductsBean.php");

class ProductsListAdd extends ProductsListAddBase
{

    protected function init() : void
    {
        $this->setBean(new ProductsBean());
        $this->setForm(new ProductInputForm());
    }

}

$template = new ProductsListAdd();

?>