<?php
include_once("store/templates/content/AccountContent.php");
include_once("store/forms/ForgotPasswordInputForm.php");
include_once("mailers/ForgotPasswordMailer.php");

/**
 * Forgotten password: a new password is sent to the e-mail.
 * The same answer for every e-mail - the page does not tell who is registered.
 */
class AccountPassword extends AccountContent
{
    protected ?InputForm $form = null;

    public function initialize() : void
    {
        $this->form = new ForgotPasswordInputForm();

        $renderer = new FormRenderer($this->form);
        $renderer->setName("ForgotPassword");
        $renderer->getSubmitButton()->setContents(tr("Изпрати"));

        $panel = new Container(false);
        $panel->setComponentClass("");
        $panel->items()->append(AccountContent::Text(tr("Въведете Вашият email aдрес от момента на регистрация в сайта и натиснете бутон 'Изпрати'")));
        $panel->items()->append(AccountContent::Text(tr("Вашата нова парола ще бъде изпратена на този адрес.")));
        $panel->items()->append($renderer);

        $this->addColumn(AccountContent::Column(tr("Забравена парола"), $panel));
    }

    public function processInput() : void
    {
        parent::processInput();

        $proc = new FormProcessor();
        $proc->process($this->form);
        if ($proc->getStatus() === IFormProcessor::STATUS_ERROR) {
            Session::SetAlert($proc->getMessage());
            return;
        }
        if ($proc->getStatus() !== IFormProcessor::STATUS_OK) return;

        $email = (string)$this->form->getInput("email")->getValue();
        //unknown e-mail, failed send: logged only
        try {
            $random_pass = Module::Active()->getAuthenticator()->setRandomPassword($email);
            $mailer = new ForgotPasswordMailer($email, $random_pass, Module::PathURL("login"));
            if (!$mailer->send()) {
                Debug::ErrorLog("Forgot password: the mail to '$email' was not sent");
            }
        }
        catch (Exception $e) {
            Debug::ErrorLog("Forgot password failed for '$email': " . $e->getMessage());
        }
        $this->redirect("login", tr("Ако този e-mail е регистриран, на него беше изпратена нова парола"));
    }

    protected function getContentTitle() : string
    {
        return tr("Забравена парола");
    }
}
