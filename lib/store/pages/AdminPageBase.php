<?php
include_once("pages/SparkAdminPage.php");
include_once("components/MenuBar.php");

include_once("components/renderers/cells/BooleanCell.php");
include_once("components/ClosureComponent.php");
include_once("utils/menu/MenuItem.php");

include_once("store/templates/admin/TemplateFactory.php");

class AdminPageBase extends SparkAdminPage
{

    public function __construct()
    {
        parent::__construct();

        MenuItem::$icon_path = STORE_LOCAL . "/images/admin/spark_icons/";

        $this->head()->addCSS(STORE_LOCAL . "/css/AdminPage.css");

        $this->head()->addMeta("viewport", "width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0");

    }

    protected function initMainMenu() : array
    {
        return TemplateFactory::MenuForPage("Main");
    }
}

?>
