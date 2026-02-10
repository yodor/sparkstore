<?php
include_once("responders/json/JSONFormResponder.php");
include_once("store/utils/SellableItem.php");
include_once("store/forms/QueryProductForm.php");
include_once("store/mailers/QueryProductMailer.php");

class QueryProductFormResponder extends JSONFormResponder
{
    protected QueryProductMailer $mailer;
    protected SellableItem $sellable;

    public function __construct(SellableItem $sellable)
    {
        parent::__construct();
        $this->mailer = new QueryProductMailer();
        $this->sellable = $sellable;
    }

    protected function createForm(): InputForm
    {
        return new QueryProductForm();
    }


    protected function onProcessSuccess(JSONResponse $resp): void
    {
        parent::onProcessSuccess($resp);
        $prodID = $this->sellable->getProductID();
        $email = "".$this->form->getInput("email")->getValue();
        $phone = "".$this->form->getInput("phone")->getValue();
        $name = $this->form->getInput("fullname")->getValue();
        $query_text = $this->form->getInput("query")->getValue();

        $this->mailer->setClient($phone, $email, $name);
        $productURL = new ProductURL();
        $productURL->setProductID($prodID);
        $this->mailer->setProduct($this->sellable->getTitle(), $productURL->fullURL());
        $this->mailer->setQueryText($query_text);
        $this->mailer->prepareMessage();
        $this->mailer->send();

        $resp->message = tr("Заявката Ви беше приета");

    }
}
?>