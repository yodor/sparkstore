<?php
include_once("storage/StorageItem.php");
include_once("components/renderers/cells/ImageCell.php");

class ProductPhotoCellRenderer extends ImageCell
{

    protected function constructItems(array $data) : void
    {
        $this->elements = array();

        if (isset($row["pclrpID"]) && $row["pclrpID"] > 0) {
            $item = new StorageItem((int)$row["pclrpID"], "ProductColorPhotosBean");
            $this->items[] = $item;
        }
        else if (isset($row["ppID"]) && $row["ppID"] > 0) {
            $item = new StorageItem((int)$row["ppID"], "ProductPhotosBean");
            $this->items[] = $item;
        }

    }

}