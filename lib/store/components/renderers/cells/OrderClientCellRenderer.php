<?php
include_once("components/renderers/cells/TableCellRenderer.php");

class OrderClientCellRenderer extends TableCellRenderer
{
    protected $userID = -1;

    protected $clients;

    public function __construct()
    {
        parent::__construct();
        $this->clients = new UsersBean();
    }

    protected function renderImpl()
    {

        $client = $this->clients->getByID($this->userID, "fullname", "email", "phone");

        echo "<div class='group client_data'>";
        echo "<div class='item fullname'>";
        echo "<label>" . tr("Име") . "</label>";
        echo "<span>" . $client["fullname"] . "</span>";
        echo "</div>";
        echo "<div class='item email'>";
        echo "<label>" . tr("E-Mail") . "</label>";
        echo "<span>" . $client["email"] . "</span>";
        echo "</div>";
        echo "<div class='item phone'>";
        echo "<label>" . tr("Телефон") . "</label>";
        echo "<span>" . $client["phone"] . "</span>";
        echo "</div>";
        echo "</div>";
    }

    public function setData(array $data) : void
    {

        parent::setData($data);

        $this->userID = (int)$data["userID"];

    }

}

?>
