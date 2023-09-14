<?php

class FilterDataInput extends DataInput {


    protected $table_prefix = "";

    protected $select = null;

    protected $prefer_all_values = false;

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


    protected function createQuery() : SQLQuery
    {
        $this->select->fields()->reset();
        $this->select->fields()->set("pcav.value", "attr.name");

        $this->select->order_by = " value ASC ";
        $this->select->group_by = " value ";

        $this->select->where()->add("attr.name" , "'".$this->getName()."'", " LIKE ");

        return new SQLQuery($this->select, "value");
    }

    protected function updateRenderer()
    {

        $renderer = $this->getRenderer();
        //echo "<HR>".$renderer->getIterator()->select->getSQL()."<HR>";
        $renderer->getItemRenderer()->setValueKey("value");
        $renderer->getItemRenderer()->setLabelKey("value");
        $renderer->na_label = "--- Всички ---";
        $renderer->setInputAttribute("onChange", "javascript:applyFilter(this)");
    }

    public function appendWhereClause(ClauseCollection $where)
    {
        $clause = new SQLClause();
        $name = $this->getName();
        $value = $this->getValue();

        $name = mb_ereg_replace("_", " ", $name);

        $clause->setExpression("(product_attributes LIKE '$name:$value%' OR  product_attributes LIKE '%$name:$value%' OR product_attributes LIKE '$name:$value%')", "", "");
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

class ProductVariantFilter extends SelectFilter {

    public function __construct(string $name, string $label, bool $required)
    {
        parent::__construct($name, $label, $required);
        $this->table_prefix = "";
    }


    protected function createQuery() : SQLQuery
    {

        $this->select->fields()->reset();
        $this->select->fields()->set("option_value");

        $this->select->order_by = " vo.option_value ASC ";
        $this->select->group_by = " vo.option_value ";

        $this->select->where()->add("vo.option_name" , "'".$this->getName()."'", " LIKE ");

        return new SQLQuery($this->select, "option_value");
    }

    protected function updateRenderer()
    {

        $renderer = $this->getRenderer();
        //echo "<HR>".$renderer->getIterator()->select->getSQL()."<HR>";
        $renderer->getItemRenderer()->setValueKey("option_value");
        $renderer->getItemRenderer()->setLabelKey("option_value");
        $renderer->na_label = "--- Всички ---";
        $renderer->setInputAttribute("onChange", "javascript:applyFilter(this)");
    }

    public function appendWhereClause(ClauseCollection $where)
    {
        $clause = new SQLClause();
        $name = $this->getName();
        $value = $this->getValue();

        $name = mb_ereg_replace("_", " ", $name);

        $clause->setExpression("(product_variants LIKE '$name:$value%' OR  product_variants LIKE '%$name:$value%' OR product_variants LIKE '$name:$value%')", "", "");
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

    protected $group_variants = NULL;
    protected $group_attributes = NULL;

    /**
     * @var SQLSelect
     */
    protected $select = NULL;

    const GROUP_VARIANTS = "variants";
    const GROUP_ATTRIBUTES = "attributes";

    public function __construct()
    {
        parent::__construct();
        $input = new BrandFilter();
        $input->getRenderer()->setInputAttribute("size", "10");
        $this->addInput($input);
//        $this->addInput(new ColorFilter());
//        $this->addInput(new SizeFilter());

        $this->getGroup(InputForm::DEFAULT_GROUP)->setDescription("Основни");
        $this->group_attributes = new InputGroup(self::GROUP_ATTRIBUTES, "Етикети");
        $this->group_variants = new InputGroup(self::GROUP_VARIANTS, "Варианти");

        $this->addGroup($this->group_attributes);
        $this->addGroup($this->group_variants);

    }

    public function setSQLSelect(SQLSelect $filters_select)
    {
        $this->select = $filters_select;

    }

    /**
     * Flag marking the current select is holding all values
     * Some filter inputs need showing all selectable values not filtered by any other filter -
     * set prefer_all_values to such filter inputs
     * @param bool $all_values
     */
    public function updateIterators(bool $all_values)
    {
        $inputs = $this->getInputs();
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

    protected function attributesSelect() : SQLSelect
    {
        $product_list = clone $this->select;
        $product_list->fields()->reset();
        $product_list->fields()->set("prodID", "pclsID");


        $select = new SQLSelect();
        $select->fields()->set("attr.name");
        $select->from = " ({$product_list->getSQL()}) as list 
        JOIN product_class_attribute_values pcav ON pcav.prodID = list.prodID 
        JOIN product_class_attributes pca ON pca.pcaID=pcav.pcaID 
        JOIN attributes attr ON attr.attrID=pca.attrID";
        $select->group_by = " attr.name ";
        $select->order_by = " attr.attrID ASC ";

        return $select;
    }

    protected function variantSelect() : SQLSelect
    {
        $product_list = clone $this->select;
        $product_list->fields()->reset();
        $product_list->fields()->set("prodID", "pclsID");


        $select = new SQLSelect();
        $select->fields()->set("vo.option_name", "vo.option_value");
        $select->from = " ({$product_list->getSQL()}) as list 
        JOIN product_variants pv ON pv.prodID = list.prodID 
        JOIN variant_options vo ON vo.voID=pv.voID 
        ";
        $select->group_by = " vo.option_name ";
        $select->order_by = " vo.voID ASC ";

        return $select;
    }

    public function createAttributeFilters()
    {

         //current product list (filtered by category or section)

        $select = $this->attributesSelect();

        $query = new SQLQuery($select, "name");
        $num = $query->exec();
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

    public function createVariantFilters()
    {

        //current product list (filtered by category or section)

        $select = $this->variantSelect();


        $query = new SQLQuery($select, "voID");
        $num = $query->exec();
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