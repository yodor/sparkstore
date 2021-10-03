<?php
include_once("responders/json/JSONResponder.php");
include_once("store/beans/SellableProducts.php");
include_once("store/mailers/QueryProductMailer.php");

class QueryProductResponder extends JSONResponder
{
    /**
     * @var int
     */
    protected $itemID = -1;

    /**
     * @var string
     */
    protected $email = "";

    /**
     * @var string
     */
    protected $name = "";

    /**
     * @var string
     */
    protected $query = "";

    /**
     * @var QueryProductMailer|null
     */
    protected $mailer = null;

    public function __construct()
    {
        parent::__construct("query_product");
        $this->itemID = -1;
        $this->email = "";
        $this->query = "";
        $this->name = "";

        $this->mailer = new QueryProductMailer();

    }

    protected function parseParams()
    {
        parent::parseParams();

        if (!isset($_REQUEST["itemID"])) throw new Exception("itemID not passed");
        $this->itemID = (int)$_REQUEST["itemID"];
        if ($this->itemID<1) throw new Exception("Incorrect itemID passed");

        if (!isset($_REQUEST["name"])) throw new Exception("name not passed");
        $this->name = strip_tags(trim($_REQUEST["name"]));
        if (!$this->name) throw new Exception("Incorrect name parameter value");

        if (!isset($_REQUEST["email"])) throw new Exception("email not passed");
        $this->email = strip_tags(trim($_REQUEST["email"]));
        if (!$this->email) throw new Exception("Incorrect email parameter value");

        if (!isset($_REQUEST["query"])) throw new Exception("query not passed");
        $this->query = strip_tags(trim($_REQUEST["query"]));
        if (!$this->query) throw new Exception("Incorrect query parameter value");
    }

    public function setMailer(QueryProductMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    protected function _sendQuery(JSONResponse $response)
    {

        $sellable = new SellableProducts();
        $query = $sellable->query("product_name", "prodID", "piID");

        $query->select->where()->add("piID", $this->itemID);
        $num = $query->exec();
        if ($num<1) throw new Exception("Incorrect itemID");

        if ($result = $query->nextResult()) {

            $prodID = $result->get("prodID");
            $piID = $result->get("piID");

            $this->mailer->setClient($this->email, $this->name);
            $this->mailer->setProduct($result->get("product_name"), fullURL(LOCAL."/products/details.php?prodID=$prodID&piID=$piID"));
            $this->mailer->setQueryText($this->query);
            $this->mailer->prepareMessage();
            $this->mailer->send();

            echo "Заявката Ви е приета";
        }
        else {
            throw new Exception("Incorrect itemID");
        }

    }
}
?>