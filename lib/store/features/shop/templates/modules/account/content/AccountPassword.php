<?php
include_once("store/features/shop/templates/modules/account/content/AccountContent.php");
include_once("store/features/shop/forms/ForgotPasswordInputForm.php");
include_once("store/beans/PasswordResetsBean.php");
include_once("mailers/PasswordResetMailer.php");
include_once("beans/UsersBean.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/processors/IFormProcessor.php");

/**
 * Forgotten password: a one-time link to set a new password is sent to the e-mail (password/reset).
 * The password is not changed here. The same answer for every e-mail - the page does not tell who is registered.
 */
class AccountPassword extends AccountContent
{
    protected ?InputForm $form = null;

    public function initialize() : void
    {
        $this->form = new ForgotPasswordInputForm();

        $renderer = new FormRenderer($this->form);
        $renderer->setName("ForgotPassword");
        $renderer->getSubmitButton()->setContents(tr("Send"));

        $panel = new Container(false);
        $panel->setComponentClass("");
        $panel->items()->append(AccountContent::Text(tr("Enter the email address you registered with and press 'Send'")));
        $panel->items()->append(AccountContent::Text(tr("You will receive a mail with a link to change your password.")));
        $panel->items()->append($renderer);

        $this->addColumn(AccountContent::Column(tr("Forgotten password"), $panel));
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

        $email = strtolower(trim((string)$this->form->getInput("email")->getValue()));
        //unknown e-mail, too frequent requests, failed send: logged only
        try {
            $userID = (new UsersBean())->email2id($email);
            if ($userID > 0) {
                $token = (new PasswordResetsBean())->create($userID);
                if (is_null($token)) {
                    Debug::ErrorLog("Password reset: a link was sent to '$email' less than " . PasswordResetsBean::THROTTLE . "s ago - no new link");
                }
                else {
                    $url = Module::PathURL("password/reset");
                    $url->add(new URLParameter("token", $token));
                    if (!(new PasswordResetMailer($email, $url))->send()) {
                        Debug::ErrorLog("Password reset: the mail to '$email' was not sent");
                    }
                }
            }
            else {
                Debug::ErrorLog("Password reset: '$email' is not registered");
            }
        }
        catch (Exception $e) {
            Debug::ErrorLog("Password reset failed for '$email': " . $e->getMessage());
        }
        $this->redirect("login", tr("If this email is registered, a link to change the password was sent to it"));
    }

    protected function getContentTitle() : string
    {
        return tr("Forgotten password");
    }
}
