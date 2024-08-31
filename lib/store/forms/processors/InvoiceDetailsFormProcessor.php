<?php
include_once("forms/processors/FormProcessor.php");
include_once("beans/DBTableBean.php");
include_once("db/BeanTransactor.php");

class InvoiceDetailsFormProcessor extends FormProcessor
{

    protected int $userID = -1;

    public function setUserID(int $userID) : void
    {
        $this->userID = $userID;
    }


    public function processImpl(InputForm $form) : void
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
