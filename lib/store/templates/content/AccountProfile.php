<?php
include_once("store/templates/content/AccountContent.php");
include_once("store/forms/RegisterClientInputForm.php");
include_once("store/forms/processors/RegisterClientFormProcessor.php");
include_once("beans/UsersBean.php");

/**
 * Profile of the client - the registration form without the e-mail (not editable) and the terms.
 * An empty password keeps the current one.
 */
class AccountProfile extends AccountContent
{
    protected ?InputForm $form = null;

    public function initialize() : void
    {
        SparkPage::Instance()->head()->addJS(Spark::Get(Config::SPARK_LOCAL) . "/js/LoginForm.js");
        SparkPage::Instance()->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL) . "/js/RegisterForm.js");

        $this->form = new RegisterClientInputForm();
        $this->form->removeInput("accept_terms");
        $this->form->getInput("token")->setValue(Module::Active()->getAuthenticator()->produceLoginToken());
        $this->loadUser();

        $renderer = new FormRenderer($this->form);
        $renderer->getSubmitButton()->setContents(tr("Запиши"));

        $this->addColumn(AccountContent::Column(tr("Клиентски профил"), $renderer));
    }

    protected function loadUser() : void
    {
        $this->form->loadBeanData(SparkPage::Instance()->getUserID(), new UsersBean());
        //not editable - not loaded from the post; readonly shows it
        $this->form->getInput("email")->setEditable(false);
        $this->form->getInput("email")->getRenderer()->input()?->setAttribute("readonly");
        $this->form->getInput("email")->getRenderer()->getAddonContainer()->items()->clear();
        $this->form->getInput("password")->setValue("");
        $this->form->getInput("password")->setRequired(false);
    }

    public function processInput() : void
    {
        parent::processInput();

        $proc = new RegisterClientFormProcessor();
        $proc->setEditID(SparkPage::Instance()->getUserID());
        $proc->process($this->form);

        if ($proc->getStatus() == IFormProcessor::STATUS_OK) {
            $this->redirect("profile", tr("Профилът беше променен успешно"));
        }
        else if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {
            Session::SetAlert($proc->getMessage());
            $this->loadUser();
        }
    }

    protected function getContentTitle() : string
    {
        return tr("Клиентски профил");
    }
}
