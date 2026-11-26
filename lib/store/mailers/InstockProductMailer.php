<?php
include_once("mailers/Mailer.php");
include_once("store/utils/url/ProductURL.php");

/**
 * A product is in stock again - to a client who asked to be notified - the mail template store/mails/instock.php
 */
class InstockProductMailer extends Mailer
{

    protected string $product_name = "";
    protected ?ProductURL $product_link = null;


    public function __construct()
    {
        parent::__construct();
        $this->subject = tr("In stock again at %s", Spark::Get(Config::SITE_DOMAIN));
    }

    public function setProduct(string $product_name, ProductURL $product_link) : void
    {
        $this->product_name = $product_name;
        $this->product_link = $product_link;
    }

    public function prepareMessage() : void
    {
        if (is_null($this->product_link) || !$this->product_name || !$this->to) {
            throw new Exception("Object initialization incomplete.");
        }
        $this->useTemplate("instock", array("product" => $this->product_name, "url" => (string)$this->product_link->fullURL()));
    }

}
