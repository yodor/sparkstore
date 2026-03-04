<?php
include_once("pages/SparkTemplateAdminPage.php");

class AdminTemplatePageBase extends SparkTemplateAdminPage
{
    public function __construct()
    {
        parent::__construct();

        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/AdminTemplatePageBase.css");

        $this->head()->addMeta("viewport", "width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0");
    }
}