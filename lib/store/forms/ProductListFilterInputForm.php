<?php

class FilterDataInput extends DataInput {


    protected $table_prefix = "";

    protected $select = null;

    public function __construct(string $name, string $label, bool $required)
    {
        parent::__construct($name, $label, $required);
        $this->setValidator(new EmptyValueValidator());
        $processor = new InputProcessor($this);

    }

    /**
     * @return string DB Table field name including prefix
     */
    protected function fieldName() : string
    {
        $name = $this->name;
        if ($this->table_prefix) {
            $name = $this->table_prefix.".".$this->name;
        }
        return $name;
    }

    /**
     *
     * Set the sql select to be used to prepare the filter iterators
     *
     * @param SQLSelect $filters_select main products sql select
     */
    public function setSQLSelect(SQLSelect $filters_select)
    {
        $this->select = clone $filters_select;

        $query = $this->createQuery();

        $renderer = $this->getRenderer();
        $renderer->setIterator($query);

        $this->updateRenderer();
    }

    /**
     * Create the default SQLQuery iterator for this field
     * @return SQLQuery
     */
    protected function createQuery() : SQLQuery
    {
        $name = $this->fieldName();
        $this->select->fields()->set($name);
        $this->select->where()->add($name , "NULL", " IS NOT ");
        $this->select->order_by = " $name ASC ";
        $this->select->group_by = " $name ";

        return new SQLQuery($this->select, $name);
    }

    protected function updateRenderer()
    {
        $renderer = $this->getRenderer();
        $renderer->getItemRenderer()->setValueKey($this->getName());
        $renderer->getItemRenderer()->setLabelKey($this->getName());
        $renderer->na_label = "--- Всички ---";
        $renderer->setInputAttribute("onChange", "javascript:applyFilter(this)");
    }

    //input name => val value posted
    public function appendWhereClause(ClauseCollection $where)
    {
        $clause = new SQLClause();
        $name = $this->fieldName();
        $clause->setExpression($name, "'".$this->getValue()."'");
        $where->addClause($clause);
    }


    public function appendHavingClause(ClauseCollection $having)
    {

    }
}

 abstract class SelectFilter extends FilterDataInput {
     public function __construct(string $name, string $label, bool $required)
     {
         parent::__construct($name, $label,$required);
         $this->setRenderer(new SelectField($this));
         $this->getProcessor()->transact_empty_string_as_null = TRUE;
     }
 }

class BrandFilter extends SelectFilter {

    public function __construct()
    {
        parent::__construct("brand_name", "Brand",0);
        $this->table_prefix = "sellable_products";
    }
}

class ColorFilter extends SelectFilter {

    public function __construct()
    {
        parent::__construct("color", "Color",0);
        $this->table_prefix = "sellable_products";
    }

}

class SizeFilter extends SelectFilter {

    public function __construct()
    {
        parent::__construct("size_value", "Size",0);
        $this->table_prefix = "sellable_products";

    }

    protected function createQuery(): SQLQuery
    {
        $query = parent::createQuery();
        $query->select->order_by = " sizing_position ASC ";

        return $query;
    }

}

class InventoryAttributeFilter extends SelectFilter {

    public function __construct(string $name, string $label, bool $required)
    {
        parent::__construct($name, $label, $required);
        $this->table_prefix = "sellable_products";
    }

    protected function createQuery() : SQLQuery
    {

       // $this->select->fields()->set("ia_value");
        $this->select->where()->add("ia_name" , "'".$this->getName()."'", " LIKE ");
        $this->select->where()->add("ia_value" , "NULL", " IS NOT ");
        $this->select->order_by = " ia_value ASC ";
        $this->select->group_by = " ia_value ";

        return new SQLQuery($this->select, "ia_value");
    }

    protected function updateRenderer()
    {
        $renderer = $this->getRenderer();
        $renderer->getItemRenderer()->setValueKey("ia_value");
        $renderer->getItemRenderer()->setLabelKey("ia_value");
        $renderer->na_label = "--- Всички ---";
        $renderer->setInputAttribute("onChange", "javascript:applyFilter(this)");
    }

    public function appendWhereClause(ClauseCollection $where)
    {

        $clause = new SQLClause();
        $name = $this->getName();
        $value = $this->getValue();

        $clause->setExpression("(inventory_attributes LIKE '$name:$value%' OR  inventory_attributes LIKE '%$name:$value%' OR inventory_attributes LIKE '$name:$value%')", "", "");
        $where->addClause($clause);


    }
    public function appendHavingClause(ClauseCollection $having)
    {
//            $clause = new SQLClause();
//            $name = $this->getName();
//            $value = $this->getValue();
//
//            $clause->setExpression("(inventory_attributes LIKE '$name:$value%' OR  inventory_attributes LIKE '%$name:$value%' OR inventory_attributes LIKE '$name:$value%')", "", "");
//            $having->addClause($clause);
    }
}

class ProductListFilterInputForm extends InputForm {

    protected $search_expressions = NULL;
    protected $compare_operators = NULL;

    /**
     * @var SQLSelect
     */
    protected $select = NULL;

    public function __construct()
    {
        parent::__construct();

        $this->addInput(new BrandFilter());
        $this->addInput(new ColorFilter());
        $this->addInput(new SizeFilter());

        //
    }

    public function setSQLSelect(SQLSelect $filters_select)
    {
        $this->select = $filters_select;

    }

    public function updateIterators()
    {
        $inputs = $this->getInputs();
        foreach ($this->inputs as $name=>$input) {
            if ($input instanceof FilterDataInput) {
                $input->setSQLSelect($this->select);
            }
        }
    }

    public function createAttributeFilters()
    {

        $query = new SQLQuery(clone $this->select);
        $query->select->fields()->reset();
        $query->select->fields()->set("ia_name");
        $query->select->group_by = " ia_name ";
        $query->select->where()->add("ia_name", "NULL", " IS NOT ");
//        echo $query->select->getSQL();

        $num = $query->exec();
        while ($result = $query->nextResult())
        {
            $filter = new InventoryAttributeFilter($result->get("ia_name"), $result->get("ia_name"),0);
            $this->addInput($filter);
        }
    }

    public function prepareClauseCollection(string $glue = SQLClause::DEFAULT_GLUE) : ClauseCollection
    {
        $where = new ClauseCollection();

        $inputs = $this->getInputs();
        foreach ($this->inputs as $name=>$input) {
            if ($input instanceof FilterDataInput) {

                $value = $input->getValue();

                if ($value > -1 && strcmp($value, "") != 0) {

                    $input->appendWhereClause($where);

                }
            }
        }
        return $where;
    }


    public function prepareHavingClause() : ClauseCollection
    {
        $having = new ClauseCollection();

        $inputs = $this->getInputs();
        foreach ($this->inputs as $name=>$input) {
            if ($input instanceof FilterDataInput) {

                $value = $input->getValue();

                if ($value > -1 && strcmp($value, "") != 0) {

                    $input->appendHavingClause($having);

                }
            }
        }
        return $having;
    }
}
?>