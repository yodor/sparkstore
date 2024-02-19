<?php
include_once("components/Component.php");
include_once("utils/IQueryFilter.php");
include_once("utils/IRequestProcessor.php");
include_once("forms/renderers/FormRenderer.php");
include_once("components/TextComponent.php");

include_once("store/forms/ProductListFilterInputForm.php");

class ProductListFilter extends FormRenderer implements IRequestProcessor
{
    protected $form;

    public function __construct()
    {
        $this->form = new ProductListFilterInputForm();

        parent::__construct($this->form);

        $this->addClassName("filters");
        $this->setAttribute("autocomplete", "off");
        $this->setMethod(FormRenderer::METHOD_GET);
        $this->getSubmitLine()->setEnabled(false);
    }
    public function resetForm()
    {
        $this->form = new ProductListFilterInputForm();
    }
    public function processInput()
    {

        $this->form->loadPostData($_GET);
        $this->form->validate();

    }

    /**
     * Return true if request data has loaded into this processor
     * @return bool
     */
    public function isProcessed(): bool
    {
        return true;
    }

    public function getForm(): ProductListFilterInputForm
    {
        return $this->form;
    }

    public function getActiveFilters() : array
    {
        $result = array();

        $filter_inputs = $this->form->getInputs();
        foreach ($filter_inputs as $name=>$input) {
            if ($input instanceof FilterDataInput) {

                $value = $input->getValue();

                if ($value > -1 && strcmp($value, "") != 0) {

                    $result[$input->getLabel()] = $value;

                }
            }
        }

        return $result;
    }
}
