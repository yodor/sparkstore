<?php
include_once("pages/SparkAdminPage.php");
include_once("components/MenuBarComponent.php");

include_once("components/renderers/cells/CallbackCellRenderer.php");
include_once("components/renderers/cells/BooleanCellRenderer.php");
include_once("components/ClosureComponent.php");

class AdminPageBase extends SparkAdminPage
{

    public function __construct()
    {
        parent::__construct();

        $this->addCSS(STORE_LOCAL . "/css/AdminPage.css");
        $this->addJS(STORE_LOCAL . "/js/MCESetupObject.js");
    }

}

?>