<?php
include_once("store/templates/modules/account/content/AccountContent.php");
include_once("store/forms/ActivateProfileInputForm.php");
include_once("store/forms/processors/ActivateProfileFormProcessor.php");

/**
 * Activation of a registered profile: the link of the activation mail (e-mail and code in the query)
 * or the form
 */
class AccountActivate extends AccountContent
{
    protected ?InputForm $form = null;

    public function initialize() : void
    {
        $this->form = new ActivateProfileInputForm();

        $renderer = new FormRenderer($this->form);
        $renderer->setMethod(FormRenderer::METHOD_GET);

        $this->addColumn(AccountContent::Column(tr("Активация на профил"), $renderer));
    }

    public function processInput() : void
    {
        parent::processInput();

        $proc = new ActivateProfileFormProcessor();
        $proc->process($this->form);

        if ($proc->getStatus() === IFormProcessor::STATUS_OK) {
            $this->redirect("login", tr("Успешна активация на профил"));
        }
        else if ($proc->getStatus() === IFormProcessor::STATUS_ERROR) {
            Session::SetAlert(tr("Възникна грешка при активация на профила") . "<div class='error'>" . htmlspecialchars($proc->getMessage()) . "</div>");
        }
    }

    protected function getContentTitle() : string
    {
        return tr("Активация на профил");
    }
}
