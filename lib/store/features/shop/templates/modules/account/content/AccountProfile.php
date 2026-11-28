<?php
include_once("store/features/shop/templates/modules/account/content/AccountContent.php");
include_once("store/features/shop/forms/RegisterClientInputForm.php");
include_once("store/features/shop/forms/processors/RegisterClientFormProcessor.php");
include_once("beans/UsersBean.php");
include_once("components/InlineScript.php");
include_once("forms/renderers/LoginFormRenderer.php");

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
        SparkPage::Instance()->head()->addTexts("The passwords do not match", "At least 6 characters", ...LoginFormScript::SCRIPT_TEXTS);

        $this->form = new RegisterClientInputForm();
        $this->form->removeInput("accept_terms");
        $this->form->getInput("token")->setValue(Module::Active()->getAuthenticator()->produceLoginToken());
        $this->loadUser();

        $renderer = new FormRenderer($this->form);
        $renderer->getSubmitButton()->setContents(tr("Save"));

        $this->addColumn(AccountContent::Column(tr("Customer profile"), $renderer));

        //challenge of a new password, the repeat check; the password is optional here
        $script = new InlineScript(true);
        $name = json_encode($this->form->getName());
        $script->setCode("const profile_form = new RegisterForm(); profile_form.passwordOptional = true; profile_form.setName($name); profile_form.initialize();");
        $renderer->items()->append($script);
    }

    protected function loadUser() : void
    {
        $this->form->loadBeanData(Module::Active()->getUserID(), new UsersBean());
        //not editable - not loaded from the post; readonly shows it
        $this->form->getInput("email")->setEditable(false);
        $this->form->getInput("email")->getRenderer()->input()?->setAttribute("readonly");
        $this->form->getInput("email")->getRenderer()->getAddonContainer()->items()->clear();
        //an empty password keeps the current one
        foreach (array("password", "password_repeat") as $name) {
            $this->form->getInput($name)->setValue("");
            $this->form->getInput($name)->setRequired(false);
        }
        $hint = $this->form->getInput("password")->getRenderer()->getAddonContainer();
        $hint->items()->clear();
        $text = new Component(false);
        $text->setText(tr("Fill in only to change the password - at least 6 characters"));
        $hint->items()->append($text);
    }

    public function processInput() : void
    {
        parent::processInput();

        $proc = new RegisterClientFormProcessor();
        $proc->setEditID(Module::Active()->getUserID());
        $proc->process($this->form);

        if ($proc->getStatus() == IFormProcessor::STATUS_OK) {
            $this->redirect("profile", tr("The profile was updated"));
        }
        else if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {
            Session::SetAlert($proc->getMessage());
            $this->loadUser();
        }
    }

    protected function getContentTitle() : string
    {
        return tr("Customer profile");
    }
}
