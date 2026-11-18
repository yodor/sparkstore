<?php
include_once("templates/BeanConfig.php");
include_once("store/templates/modules/checkout/content/CheckoutContent.php");
include_once("store/forms/processors/UserRowFormProcessor.php");
include_once("forms/renderers/FormRenderer.php");

/**
 * Checkout step editing the client's own row of a bean with a 'userID' column (address, courier office,
 * invoice details) - TemplateConfig: beanClass, formClass, title. Saved: saved() and confirm.
 */
class CheckoutRowEditor extends CheckoutContent
{

    /**
     * Works on a bean - BeanConfig
     * @return string
     */
    public static function ConfigClass() : string
    {
        return BeanConfig::class;
    }
    protected ?InputForm $form = null;

    protected int $editID = -1;

    /**
     * Css class of the page 'base' container (checkout.css)
     * @var string
     */
    protected string $baseClass = "";

    public function initialize() : void
    {
        $this->ensureCartItems();
        $this->check();

        if (is_null($this->bean)) throw new Exception("TemplateConfig beanClass is empty");
        $form = SparkLoader::Factory(SparkLoader::PREFIX_FORMS)->instance($this->config->formClass, InputForm::class);
        if (!($form instanceof InputForm)) throw new Exception("TemplateConfig formClass is not an InputForm");
        $this->form = $form;

        $row = $this->bean->getResult("userID", (string)Module::Active()->getUserID(), $this->bean->key());
        if (is_array($row)) {
            $this->editID = (int)$row[$this->bean->key()];
            $this->form->loadBeanData($this->editID, $this->bean);
        }

        $page = $this->page();
        $page->getCartComponent()->setRenderEnabled(false);

        $renderer = new FormRenderer($this->form);
        $renderer->getSubmitLine()->setRenderEnabled(false);
        if ($this->baseClass !== "") $page->base()->addClassName($this->baseClass);
        $page->base()->items()->append($renderer);

        $name = json_encode($this->form->getName());
        $this->navigation(CheckoutContent::NAV_LEFT, tr("Назад"), "edit", $this->backURL());
        $this->navigation(CheckoutContent::NAV_RIGHT, tr("Продължи"), "checkout", "javascript:document.forms[$name].submit();");
    }

    /**
     * Before the form: redirect when the step does not apply (no courier ...)
     * @return void
     */
    protected function check() : void
    {
    }

    /**
     * After the row is saved - the cart selection of the step
     * @return void
     */
    protected function saved() : void
    {
    }

    public function processInput() : void
    {
        parent::processInput();

        $proc = new UserRowFormProcessor();
        $proc->setBean($this->bean);
        $proc->setEditID($this->editID);
        $proc->setUserID(Module::Active()->getUserID());
        $this->form->setProcessor($proc);

        $proc->process($this->form);

        if ($proc->getStatus() == IFormProcessor::STATUS_OK) {
            $this->saved();
            $this->redirect("confirm");
        }
        else if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {
            Session::SetAlert($proc->getMessage());
        }
    }
}
