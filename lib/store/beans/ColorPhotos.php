<?php
include_once("class/utils/ColorPhotosSQL.php");
include_once("beans/DBViewBean.php");

class ColorPhotos extends DBViewBean
{
    protected $colorPhotos = null;
    public function __construct()
    {
        $this->colorPhotos  = new ColorPhotosSQL();
        $this->createString = "CREATE VIEW IF NOT EXISTS color_photos AS ({$this->colorPhotos->getSQL()})";
        parent::__construct("color_photos");

        $this->select->fields()->set(...$this->columnNames());
        $this->prkey = "pclrpID";
    }

}
?>