<?php
include_once("store/templates/modules/account/content/AccountContent.php");
include_once("store/beans/PasswordResetsBean.php");
include_once("beans/UsersBean.php");
include_once("auth/PasswordHash.php");
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/processors/IFormProcessor.php");

/**
 * New password from the link of the password reset mail (?token=). The link works once and for
 * PasswordResetsBean::LIFETIME seconds.
 */
class AccountPasswordReset extends AccountContent
{
    const int MIN_LENGTH = 6;

    protected ?InputForm $form = null;

    protected int $userID = -1;

    protected ?PasswordResetsBean $resets = null;

    public function initialize() : void
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Referrer-Policy: no-referrer");

        $this->resets = new PasswordResetsBean();
        $this->userID = $this->resets->userID((string)($_GET["token"] ?? ""));
        if ($this->userID < 1) {
            $this->redirect("password", tr("Връзката за смяна на паролата е невалидна или изтекла. Моля, заявете нова."));
        }

        $this->form = new InputForm();
        $this->form->setName("PasswordResetForm");
        $this->form->addInput(DataInputFactory::Create(InputType::PASSWORD, "password", tr("Нова парола"), 1));
        $this->form->addInput(DataInputFactory::Create(InputType::PASSWORD, "password_repeat", tr("Повторете новата парола"), 1));

        $renderer = new FormRenderer($this->form);
        $renderer->setAttribute("autocomplete", "off");
        $renderer->getSubmitButton()->setContents(tr("Запиши"));

        $panel = new Container(false);
        $panel->setComponentClass("");
        $panel->items()->append(AccountContent::Text(tr("Въведете новата парола - поне 6 символа.")));
        $panel->items()->append($renderer);

        $this->addColumn(AccountContent::Column(tr("Смяна на парола"), $panel));
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

        $password = (string)$this->form->getInput("password")->getValue();
        $repeat = (string)$this->form->getInput("password_repeat")->getValue();
        $this->form->getInput("password")->setValue("");
        $this->form->getInput("password_repeat")->setValue("");

        if (mb_strlen($password) < AccountPasswordReset::MIN_LENGTH) {
            Session::SetAlert(tr("Невалидна дължина на парола"));
            return;
        }
        if (strcmp($password, $repeat) !== 0) {
            Session::SetAlert(tr("Паролите не съвпадат"));
            return;
        }

        $users = new UsersBean();
        $users->checkPasswordColumn();
        $hash = new PasswordHash($password);
        if (!$users->update($this->userID, array("password" => $hash->getValue()))) {
            Debug::ErrorLog("Password reset: update of user {$this->userID} failed: " . $users->getDB()->getError());
            Session::SetAlert(tr("Паролата не беше сменена. Моля, опитайте отново по-късно."));
            return;
        }
        //used - the link does not work again
        $this->resets->clear($this->userID);

        $this->redirect("login", tr("Паролата беше сменена успешно. Влезте с новата парола."));
    }

    protected function getContentTitle() : string
    {
        return tr("Смяна на парола");
    }
}
