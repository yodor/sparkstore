<?php

class FilterDataInput extends DataInput {


    protected string $table_prefix = "";

    protected ?SQLSelect $select = null;

    protected bool $prefer_all_values = false;

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
    public function setSQLSelect(SQLSelect $filters_select) : void
    {
        $this->select = clone $filters_select;

        $query = $this->createQuery();

        $renderer = $this->getRenderer();
        $renderer->setIterator($query);

        $this->updateRenderer();
    }

    /**
     * Create the default SelectQuery iterator for this field
     * @return SelectQuery
     * @throws Exception
     */
    protected function createQuery() : SelectQuery
    {
        $name = $this->fieldName();
        //if (!InputSanitizer::SafeSQLColumn($name)) throw new Exception("Incorrect column name: $name");
        $this->select->set($name);
        $this->select->where()->expression("$name IS NOT NULL");
        //check usage name should be sanitized
        $this->select->order($name, OrderDirection::ASC);
        $this->select->group_by = " $name ";

        return new SelectQuery($this->select, $name);
    }

    protected function updateRenderer() : void
    {
        $renderer = $this->getRenderer();
        $renderer->getItemRenderer()->setValueKey($this->getName());
        $renderer->getItemRenderer()->setLabelKey($this->getName());
        if ($renderer instanceof SelectField) {
            $renderer->setDefaultOption("--- Всички ---");
        }
        $renderer->input()?->setAttribute("onChange", "javascript:applyFilter(this)");
    }

    //input name => val value posted
    public function appendWhereClause(ClauseCollection $where) : void
    {
        $where->match($this->fieldName(), $this->getValue());
    }


    public function appendHavingClause(ClauseCollection $having) : void
    {

    }
    public function preferAllValues() : bool {
        return $this->prefer_all_values;
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
        //$this->table_prefix = "sellable_products";
        $this->prefer_all_values = true;
    }

}

class ProductAttributeFilter extends SelectFilter {

    public function __construct(string $name, string $label, bool $required)
    {
        parent::__construct($name, $label, $required);
        $this->table_prefix = "";
    }


    protected function createQuery() : SelectQuery
    {
        $this->select->reset();
        $this->select->set("pcav.value", "attr.name");

        $this->select->order("value", OrderDirection::ASC);
        $this->select->group_by = " value ";

        $this->select->where()->match("attr.name" , $this->getName(), " LIKE ");

        return new SelectQuery($this->select, "value");
    }

    protected function updateRenderer() : void
    {

        $renderer = $this->getRenderer();
        //echo "<HR>".$renderer->getIterator()->select->getSQL()."<HR>";
        $renderer->getItemRenderer()->setValueKey("value");
        $renderer->getItemRenderer()->setLabelKey("value");
        if ($renderer instanceof SelectField) {
            $renderer->setDefaultOption("--- Всички ---");
        }
        $renderer->input()?->setAttribute("onChange", "javascript:applyFilter(this)");
    }

    public function appendWhereClause(ClauseCollection $where) : void
    {
        $clause = new SQLClause();
        $name = $this->getName();
        $value = $this->getValue();

        $name = mb_ereg_replace("_", " ", $name);

        $clause->setExpression("product_attributes LIKE :AttributeNameValue");
        $clause->bind(":AttributeNameValue", "%$name:$value%");
        $where->append($clause);
    }
    public function appendHavingClause(ClauseCollection $having) : void
    {

    }
}

class ProductVariantFilter extends SelectFilter {

    public function __construct(string $name, string $label, bool $required)
    {
        parent::__construct($name, $label, $required);
        $this->table_prefix = "";
    }


    protected function createQuery() : SelectQuery
    {

        $this->select->reset();
        $this->select->set("option_value");

        $this->select->order("vo.option_value", OrderDirection::ASC);
        $this->select->group_by = " vo.option_value ";

        $this->select->where()->match("vo.option_name", $this->getName(), " LIKE ");

        return new SelectQuery($this->select, "option_value");
    }

    protected function updateRenderer() : void
    {

        $renderer = $this->getRenderer();
        //echo "<HR>".$renderer->getIterator()->select->getSQL()."<HR>";
        $renderer->getItemRenderer()->setValueKey("option_value");
        $renderer->getItemRenderer()->setLabelKey("option_value");
        if ($renderer instanceof SelectField) {
            $renderer->setDefaultOption("--- Всички ---");
        }

        $renderer->input()?->setAttribute("onChange", "javascript:applyFilter(this)");
    }

    public function appendWhereClause(ClauseCollection $where) : void
    {
        $clause = new SQLClause();
        $name = $this->getName();
        $value = $this->getValue();

        $name = mb_ereg_replace("_", " ", $name);

        $clause->setExpression("(product_variants LIKE :VariantNameValue)");
        $clause->bind(":VariantNameValue", "%$name:$value%");
        $where->append($clause);
    }
    public function appendHavingClause(ClauseCollection $having) : void
    {

    }
}

class ProductListFilterInputForm extends InputForm {

    protected $search_expressions = NULL;
    protected $compare_operators = NULL;

    protected $group_variants = NULL;
    protected $group_attributes = NULL;

    /**
     * @var SQLSelect
     */
    protected ?SQLSelect $select = NULL;

    const GROUP_VARIANTS = "variants";
    const GROUP_ATTRIBUTES = "attributes";

    public function __construct()
    {
        parent::__construct();
        $input = new BrandFilter();
        $input->getRenderer()->input()?->setAttribute("size", "1");
        $this->addInput($input);
//        $this->addInput(new ColorFilter());
//        $this->addInput(new SizeFilter());

        $this->getGroup(InputForm::DEFAULT_GROUP)->setTitle("Основни");
        $this->group_attributes = new InputGroup(self::GROUP_ATTRIBUTES, "Етикети");
        $this->group_variants = new InputGroup(self::GROUP_VARIANTS, "Варианти");

        $this->addGroup($this->group_attributes);
        $this->addGroup($this->group_variants);

    }

    public function setSQLSelect(SQLSelect $filters_select) : void
    {
        $this->select = $filters_select;

    }

    /**
     * Flag marking the current select is holding all values
     * Some filter inputs need showing all selectable values not filtered by any other filter -
     * set prefer_all_values to such filter inputs
     * @param bool $all_values
     */
    public function updateIterators(bool $all_values) : void
    {
        $inputs = $this->inputs();
        foreach ($this->inputs as $name=>$input) {
            if ($input instanceof FilterDataInput) {

                if (!$all_values && $input->preferAllValues())continue;
                if ($all_values && !$input->preferAllValues())continue;

                if ($input instanceof ProductAttributeFilter) {
                    $input->setSQLSelect($this->attributesSelect());
                }
                else if ($input instanceof ProductVariantFilter) {
                    $input->setSQLSelect($this->variantSelect());
                }
                else {
                    $input->setSQLSelect($this->select);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function attributesSelect() : SQLSelect
    {
        $product_list = clone $this->select;
        $product_list->reset();
        $product_list->set("prodID", "pclsID");

        $select = new SQLSelect();
        $select->set("attr.name");
        $select->from(" ({$product_list->getSQL()}) as list ")
            ->join("product_class_attribute_values pcav")->on("pcav.prodID = list.prodID")
            ->join("product_class_attributes pca")->on("pca.pcaID=pcav.pcaID")
            ->join("attributes attr")->on("attr.attrID=pca.attrID");

        $select->group_by = " attr.name ";
        $select->order("attr.attrID", OrderDirection::ASC);

        return $select;
    }

    /**
     * @throws Exception
     */
    protected function variantSelect() : SQLSelect
    {
        $product_list = clone $this->select;
        $product_list->reset();
        $product_list->set("prodID", "pclsID");

        $select = new SQLSelect();
        $select->set("vo.option_name", "vo.option_value");
        $select->from(" ({$product_list->getSQL()}) as list")
            ->join("product_variants pv")->on("pv.prodID = list.prodID")
            ->join("variant_options vo")->on("vo.voID = pv.voID");

        $select->group_by = " vo.option_name ";

        $select->order("vo.voID", OrderDirection::ASC);

        return $select;
    }

    public function createAttributeFilters() : void
    {

         //current product list (filtered by category or section)

        $select = $this->attributesSelect();

        $query = new SelectQuery($select, "name");
        $query->exec();
        while ($result = $query->nextResult())
        {

            $name = $result->get("name");

            //replace spaces with underscores
            $name = mb_ereg_replace(" ", "_", $name);

            $filter = new ProductAttributeFilter($name, $result->get("name"), 0);
            //just add input - no iterator yet, to pass processInput and possible addition filtering
            $this->addInput($filter, $this->group_attributes);
        }
    }

    public function createVariantFilters() : void
    {

        //current product list (filtered by category or section)

        $select = $this->variantSelect();


        $query = new SelectQuery($select, "voID");
        $query->exec();
        while ($result = $query->nextResult())
        {
            $name = $result->get("option_name");
            //replace spaces with underscores
            $name = mb_ereg_replace(" ", "_", $name);

            $filter = new ProductVariantFilter($name, $result->get("option_name"), 0);
            //just add input - no iterator yet, to pass processInput and possible addition filtering
            $this->addInput($filter, $this->group_variants);
        }
    }

    public function prepareClauseCollection(string $glue = SQLClause::DEFAULT_GLUE) : ClauseCollection
    {
        $where = new ClauseCollection();

        $inputs = $this->inputs();
        foreach ($this->inputs as $name=>$input) {
            if ($input instanceof FilterDataInput) {

                $value = $input->getValue();

                if ($value > -1 && strcmp($value, "") !== 0) {

                    $input->appendWhereClause($where);

                }
            }
        }
        return $where;
    }


    public function prepareHavingClause() : ClauseCollection
    {
        $having = new ClauseCollection();

        $inputs = $this->inputs();
        foreach ($this->inputs as $name=>$input) {
            if ($input instanceof FilterDataInput) {

                $value = $input->getValue();

                if ($value > -1 && strcmp($value, "") !== 0) {

                    $input->appendHavingClause($having);

                }
            }
        }
        return $having;
    }
}