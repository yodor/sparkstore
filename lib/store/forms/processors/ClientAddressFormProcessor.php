<?php
include_once("forms/processors/FormProcessor.php");
include_once("beans/DBTableBean.php");
include_once("db/BeanTransactor.php");

class ClientAddressFormProcessor extends FormProcessor
{
    protected $bean = NULL;
    protected $editID = -1;
    protected $userID = -1;

    public function setUserID(int $userID)
    {
        $this->userID = $userID;
    }

    public function processImpl(InputForm $form)
    {

        parent::processImpl($form);

        if ($this->getStatus() != IFormProcessor::STATUS_OK) return;

        if ($this->userID < 1) throw new Exception("Тази функция изисква регистрация");

        $dbt = new BeanTransactor($this->bean, $this->editID);
        $dbt->appendValue("userID", $this->userID);

        $dbt->processForm($form);

        $dbt->processBean();

    }
}

?>
