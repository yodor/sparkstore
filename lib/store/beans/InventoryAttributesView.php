<?php
include_once("beans/DBViewBean.php");

class InventoryAttributesView extends DBViewBean
{
    protected $createString = "";

    public function __construct()
    {
        parent::__construct("inventory_attributes");
    }

}

?>
