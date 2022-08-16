<?php
include_once("mailers/Mailer.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/InputForm.php");
include_once("store/mailers/ContactRequestMailer.php");


class ContactRequestProcessor extends FormProcessor
{

    protected $mailer = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->mailer = new ContactRequestMailer();
    }

    protected function processImpl(InputForm $form)
    {
        parent::processImpl($form);

        if ($form->haveInput("fullname")) {
            $name = $form->getInput("fullname")->getValue();
            $this->mailer->setClient($name);
        }

        if ($form->haveInput("query")) {
            $query = $form->getInput("query")->getValue();
            $this->mailer->setQueryText($query);
        }

        $this->mailer->prepareMessage();
        $success = $this->mailer->send();
        debug ("Mail accepted for delivery: ".(int)$success);
    }

}

?>