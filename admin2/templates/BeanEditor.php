<?php
include_once("components/templates/admin/AdminPageTemplate.php");
include_once("components/BeanFormEditor.php");
/**
 * If request condition is BeanKeyCondition will use it to set where filter on the view bean and add field to the transactor
 * Class BeanEditorPage
 */
class BeanEditor extends TemplateContent
{

    /**
     * @var InputForm|null
     */
    protected ?InputForm $form = null;

    public function __construct()
    {
        parent::__construct();
    }



    public function setForm(InputForm $form) : void
    {
        $this->form = $form;
    }

    public function getForm(): ?InputForm
    {
        return $this->form;
    }

    public function processInput() : void
    {
        $this->cmp->processInput();
    }

    public function initialize(): void
    {

        $this->cmp = new BeanFormEditor($this->bean, $this->form);

        if ($this->request_condition instanceof BeanKeyCondition) {
            $this->bean->select()->where()->addURLParameter($this->request_condition->getURLParameter());
            $this->cmp->getTransactor()->appendURLParameter($this->request_condition->getURLParameter());
        }

    }

    public function editor(): BeanFormEditor
    {
        if ($this->cmp instanceof BeanFormEditor) return $this->cmp;
        throw new Exception("Incorrect component class - expected BeanFormEditor");
    }

    public function configure(TemplateConfig $config)
    {
        parent::configure($config);
        if ($config->formClass) {
            Spark::LoadBeanClass($config->formClass);
            $this->setForm(new $config->formClass());
        }

        if (!$config->title) {
            $config->title = "Item Data: ".get_class($this->bean);
        }

    }

}