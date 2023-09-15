<?php
include_once("pages/SparkAdminPage.php");
include_once("components/MenuBarComponent.php");

include_once("components/renderers/cells/CallbackCellRenderer.php");
include_once("components/renderers/cells/BooleanCellRenderer.php");
include_once("components/ClosureComponent.php");
include_once("utils/MenuItem.php");

include_once("store/templates/admin/TemplateFactory.php");

class AdminPageBase extends SparkAdminPage
{

    public function __construct()
    {
        parent::__construct();

        MenuItem::$icon_path = STORE_LOCAL . "/images/admin/spark_icons/";

        $this->addCSS(STORE_LOCAL . "/css/AdminPage.css");
        $this->addJS(STORE_LOCAL . "/js/MCESetupObject.js");

        $this->addMeta("viewport", "width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0");

    }

    protected function initMainMenu() : array
    {
        return TemplateFactory::MenuForPage("Main");
    }
}

?>