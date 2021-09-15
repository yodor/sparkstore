<?php
include_once("forms/processors/FormProcessor.php");
include_once("beans/DBTableBean.php");
include_once("db/BeanTransactor.php");

class InvoiceDetailsFormProcessor extends FormProcessor
{
    protected $bean = NULL;
    protected $editID = -1;
    protected $userID = -1;

    public function setEditID(int $editID)
    {
        $this->editID = $editID;
    }

    public function setUserID(int $userID)
    {
        $this->userID = (int)$userID;
    }

    public function setBean(DBTableBean $bean)
    {
        $this->bean = $bean;
    }

    public function processImpl(InputForm $form)
    {

        parent::processImpl($form);

        if ($this->getStatus() != FormProcessor::STATUS_OK) return;

        if ($this->userID < 1) throw new Exception("Тази функция изисква регистрация");

        $dbt = new BeanTransactor($this->bean, $this->editID);
        $dbt->appendValue("userID", $this->userID);

        $dbt->processForm($form);

        $dbt->processBean();

    }
}

?>
