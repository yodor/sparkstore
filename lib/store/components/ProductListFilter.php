<?php
include_once("components/Component.php");
include_once("utils/IQueryFilter.php");
include_once("utils/IRequestProcessor.php");
include_once("forms/renderers/FormRenderer.php");
include_once("components/TextComponent.php");

include_once("objects/SparkMap.php");
include_once("objects/sql/ClosureFilter.php");
include_once("sql/SQLSelect.php");

class ProductListFilter extends FormRenderer implements IRequestProcessor, ISQLSelectProcessor
{

    protected ?SQLSelect $select = null;
    /**
     * Form input name to closure map
     * @var SparkMap
     */
    protected SparkMap $extended_search;

    public function __construct(InputForm $form)
    {
        parent::__construct($form);

        $this->extended_search = new SparkMap();

        $this->addClassName("filters");
        $this->setAttribute("autocomplete", "off");
        $this->setMethod(FormRenderer::METHOD_GET);
        $this->getSubmitButton()->setContents("Search");
        $this->getSubmitLine()->items()->append(Button::ActionButton("Изчисти", "clearFilters()"));

    }

    public function processInput()
    {
        $this->form->loadPostData($_GET);
        $this->form->validate();

        $iterator = $this->extended_search->iterator();
        while ($filter = $iterator->next()) {
            if ($filter instanceof ClosureFilter) {
                $filter->process($this->select, $this->form->getInput($filter->getName()));
            }
        }
    }

    public function addFilter(string $name, ClosureFilter $closure) : void
    {
        if (!$this->form->haveInput($name)) throw new Exception("DataInput '$name' not found");
        $closure->setName($name);
        $this->extended_search->add($name, $closure);
    }

    public function getFilter(string $name) : ClosureFilter
    {
        $result = $this->extended_search->get($name);
        if ($result instanceof ClosureFilter) return $result;
        throw new Exception("Incorrect filter '$name'");
    }

    public function getFilterNames() : array
    {
        return $this->extended_search->keys();
    }

    public function getFilters() : SparkMap
    {
        return $this->extended_search;
    }

    /**
     *
     * Return true if request data has loaded into this processor
     * @return bool
     */
    public function isProcessed(): bool
    {
        $result = true;
        if ($this->extended_search->count()>0) {
            $result = false;
            foreach ($this->extended_search as $filter) {
                if ($filter->isProcessed()) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    public function getForm(): InputForm
    {
        return $this->form;
    }

    public function getActiveFilters() : array
    {
        $result = array();

        $filter_inputs = $this->form->inputs();
        foreach ($filter_inputs as $name=>$input) {
            if ($input instanceof DataInput) {

                $value = $input->getValue();

                if ($value > -1 && strcmp($value, "") != 0) {

                    $label = $input->getLabel();
                    $name = $input->getName();
                    if ($this->extended_search->isSet($name)) {
                        $filter = $this->extended_search->get($name);
                        if ($filter instanceof ClosureFilter) {
                            if (strcmp($filter->getTitle(), $label)!==0) {
                                $label = $filter->getTitle();
                            }
                        }
                    }
                    $result[$label] = $value;

                }
            }
        }

        return $result;
    }

    public function setSQLSelect(SQLSelect $select): void
    {
        $this->select = $select;
    }

    public function getSQLSelect(): ?SQLSelect
    {
        return $this->select;
    }
}
