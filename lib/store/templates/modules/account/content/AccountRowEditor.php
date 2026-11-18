<?php
include_once("templates/BeanConfig.php");
include_once("store/templates/modules/account/content/AccountContent.php");
include_once("store/forms/processors/UserRowFormProcessor.php");
include_once("forms/renderers/FormRenderer.php");

/**
 * Editor of the client's own row of a bean with a 'userID' column - one row per client (address, invoice details).
 * TemplateConfig: beanClass, formClass, title.
 */
class AccountRowEditor extends AccountContent
{

    /**
     * Works on a bean - BeanConfig
     * @return string
     */
    public static function ConfigClass() : string
    {
        return BeanConfig::class;
    }

    /**
     * The bean of the row (BeanConfig beanClass) - this content keeps the layout base of its module
     * @var DBTableBean|null
     */
    protected ?DBTableBean $bean = null;

    public function setup(TemplateConfig $config): void
    {
        parent::setup($config);
        if ($config instanceof BeanConfig) $this->bean = $config->createBean();
    }
    protected ?InputForm $form = null;

    protected int $editID = -1;

    public function initialize() : void
    {
        if (is_null($this->bean)) throw new Exception("TemplateConfig beanClass is empty");
        $form = SparkLoader::Factory(SparkLoader::PREFIX_FORMS)->instance($this->config->formClass, InputForm::class);
        if (!($form instanceof InputForm)) throw new Exception("TemplateConfig formClass is not an InputForm");
        $this->form = $form;

        $row = $this->bean->getResult("userID", (string)Module::Active()->getUserID(), $this->bean->key());
        if (is_array($row)) {
            $this->editID = (int)$row[$this->bean->key()];
            $this->form->loadBeanData($this->editID, $this->bean);
        }

        $renderer = new FormRenderer($this->form);
        $renderer->getSubmitButton()->setContents(tr("Запиши"));
        $this->addColumn(AccountContent::Column($this->config->title, $renderer));
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
            $this->redirect(str_replace(".", "/", $this->config->filename), tr("Промените бяха записани успешно"));
        }
        else if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {
            Session::SetAlert($proc->getMessage());
        }
    }
}
