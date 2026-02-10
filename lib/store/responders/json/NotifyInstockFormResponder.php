<?php
include_once("responders/json/JSONFormResponder.php");
include_once("store/utils/SellableItem.php");
include_once("store/forms/NotifyInstockForm.php");
include_once("iterators/ArrayDataIterator.php");
include_once("input/validators/NumericValidator.php");
include_once("store/beans/InstockSubscribersBean.php");

class NotifyInstockFormResponder extends JSONFormResponder
{
    /**
     * @var SellableItem
     */
    protected SellableItem $sellable;

    public function __construct(SellableItem $sellable)
    {
        parent::__construct();
        $this->sellable = $sellable;
    }

    protected function createForm(): InputForm
    {
        return new NotifyInstockForm();
    }

    protected function onProcessSuccess(JSONResponse $resp): void
    {
        parent::onProcessSuccess($resp);
        $email = $this->form->getInput("email")->getValue();

        $bean = new InstockSubscribersBean();
        $query = $bean->query("email", "prodID");

        $query->select->where()->add("email", "'$email'");
        $query->select->where()->add("prodID", $this->sellable->getProductID());
        $num = $query->exec();
        if ($num>0) {
            $resp->message = tr("Вече сте абониран");
            return;
        }
        $data = array("email"=>$email, "prodID"=>$this->sellable->getProductID());

        $bean->insert($data);

        $resp->message = tr("Заявката Ви беще приета");
    }

}
?>