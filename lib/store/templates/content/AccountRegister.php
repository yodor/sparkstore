<?php
include_once("store/templates/content/AccountContent.php");
include_once("store/forms/RegisterClientInputForm.php");
include_once("store/forms/processors/RegisterClientFormProcessor.php");
include_once("components/InlineScript.php");

/**
 * Registration of a new client - the activation mail is sent by RegisterClientFormProcessor
 */
class AccountRegister extends AccountContent
{
    protected ?InputForm $form = null;

    public function initialize() : void
    {
        SparkPage::Instance()->head()->addJS(Spark::Get(Config::SPARK_LOCAL) . "/js/LoginForm.js");
        SparkPage::Instance()->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL) . "/js/RegisterForm.js");

        $this->form = new RegisterClientInputForm();
        $this->form->getInput("token")->setValue(Module::Active()->getAuthenticator()->produceLoginToken());

        $renderer = new FormRenderer($this->form);
        $renderer->setAttribute("autocomplete", "off");
        $renderer->getSubmitButton()->setContents(tr("Регистрация"));

        $component = new Container(false);
        $component->setComponentClass("register_component");
        $component->items()->append($renderer);
        $this->addColumn(AccountContent::Column(tr("Нова регистрация"), $component, "register"));

        $script = new InlineScript(true);
        $name = json_encode($this->form->getName());
        $script->setCode("const register_form = new RegisterForm(); register_form.setName($name); register_form.initialize();");
        $component->items()->append($script);
    }

    public function processInput() : void
    {
        parent::processInput();

        $proc = new RegisterClientFormProcessor();
        $proc->process($this->form);

        if ($proc->getStatus() == IFormProcessor::STATUS_OK) {
            $this->redirect("register/complete");
        }
        else if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {
            sleep(3);
            Session::SetAlert($proc->getMessage());
        }
    }

    protected function getContentTitle() : string
    {
        return tr("Регистрация");
    }
}
