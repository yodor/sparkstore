<?php
include_once("forms/processors/FormProcessor.php");
include_once("beans/DBTableBean.php");
include_once("db/BeanTransactor.php");

/**
 * Insert or update the row of the client in a bean with a 'userID' column (address, invoice details).
 * setBean(), setEditID() (the row of the client or -1 to insert) and setUserID() before process().
 */
class UserRowFormProcessor extends FormProcessor
{

    protected int $userID = -1;

    public function setUserID(int $userID) : void
    {
        $this->userID = $userID;
    }

    public function processImpl(InputForm $form) : void
    {
        parent::processImpl($form);

        if ($this->getStatus() != IFormProcessor::STATUS_OK) return;

        if ($this->userID < 1) throw new Exception(tr("This function needs a registration"));

        $dbt = new BeanTransactor($this->bean, $this->editID);
        $dbt->appendValue("userID", $this->userID);

        $dbt->processForm($form);

        $dbt->processBean();
    }
}
