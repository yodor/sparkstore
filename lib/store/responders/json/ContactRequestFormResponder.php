<?php
include_once("responders/json/JSONFormResponder.php");
include_once("store/forms/ContactRequestForm.php");
include_once("store/beans/ContactRequestsBean.php");
include_once("store/mailers/ContactRequestMailer.php");

class ContactRequestFormResponder extends JSONFormResponder
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function createForm(): InputForm
    {
        return new ContactRequestForm();
    }

    protected function onProcessSuccess(JSONResponse $resp)
    {
        parent::onProcessSuccess($resp);

        $fullname = $this->form->getInput("fullname")->getValue();
        $email = $this->form->getInput("email")->getValue();
        $query = $this->form->getInput("query")->getValue();

        $db = DBConnections::Open();
        try {
            $db->transaction();

            $bean = new ContactRequestsBean();
            $data = array("fullname"=>$fullname, "email"=>$email, "query"=>$query);
            if ($bean->insert($data) < 1) throw new Exception("DB Error: ".$db->getError());

            $db->commit();

            $mailer = new ContactRequestMailer();
            $mailer->setClient($fullname);
            $mailer->setEmail($email);
            $mailer->setQueryText($query);
            $mailer->prepareMessage();
            $success = $mailer->send();
            Debug::ErrorLog ("Mail accepted for delivery: ".(int)$success);

            $resp->message = tr("Заявката Ви беше приета успешно");
        }
        catch (Exception $e) {
            Debug::ErrorLog("Unable to insert contact request");
            $db->rollback();
            throw $e;
        }


    }
}

?>
