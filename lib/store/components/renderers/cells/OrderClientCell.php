<?php
include_once("components/renderers/cells/TableCell.php");

class OrderClientCell extends TableCell
{
    protected $userID = -1;

    protected $clients;

    public function __construct()
    {
        parent::__construct();
        $this->clients = new UsersBean();
    }

    /**
     * Client data from the row ('client' = "fullname--email--phone" from OrderListSQL) - one query per row only
     * when the row does not have it
     * @var array|null
     */
    protected ?array $client = null;

    protected function renderImpl(): void
    {
        $client = $this->client ?? $this->clients->getByID($this->userID, "fullname", "email", "phone");

        echo "<div class='group client_data'>";
        foreach (array("fullname" => tr("Име"), "email" => tr("E-Mail"), "phone" => tr("Телефон")) as $key => $label) {
            echo "<div class='item $key'>";
            echo "<label>" . $label . "</label>";
            //entered by the client
            echo "<span>" . htmlspecialchars((string)($client[$key] ?? "")) . "</span>";
            echo "</div>";
        }
        echo "</div>";
    }

    public function setData(array $data) : void
    {

        parent::setData($data);

        $this->userID = (int)$data["userID"];

        $this->client = null;
        if (isset($data["client"])) {
            $parts = explode("--", (string)$data["client"]);
            $this->client = array("fullname" => $parts[0] ?? "", "email" => $parts[1] ?? "", "phone" => $parts[2] ?? "");
        }

    }

}